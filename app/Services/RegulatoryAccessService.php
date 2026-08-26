<?php

namespace App\Services;

use App\Models\Form;
use App\Models\RegulatoryFormSubmission;
use App\Models\UserRoleRequest;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RegulatoryAccessService
{
    public const LEVELS = ['primary', 'secondary', 'tertiary', 'quaternary', 'extra1'];
    private const FINAL_OR_REVIEW_STATUSES = ['pending', 'approved'];

    public function userHasActiveRole(User $user, int $roleCatalogId): bool
    {
        return UserRoleRequest::where('user_id', $user->id)
            ->where('role_catalog_id', $roleCatalogId)
            ->where('status', UserRoleRequest::STATUS_ACTIVE)
            ->exists();
    }

    public function assertUserHasActiveRole(User $user, int $roleCatalogId): void
    {
        abort_unless(
            $this->userHasActiveRole($user, $roleCatalogId),
            403,
            'This regulatory form requires an active role.'
        );
    }

    public function accessibleFormForUser(User $user, int $formId): Form
    {
        $form = Form::where('id', $formId)
            ->where('connect_regulatory', true)
            ->where('enable', true)
            ->whereNotNull('regulatory_role_catalog_id')
            ->whereIn('regulatory_level', self::LEVELS)
            ->where($this->countryEligibilityQuery($user))
            ->with(['fields.options'])
            ->firstOrFail();

        $this->assertUserHasActiveRole($user, (int) $form->regulatory_role_catalog_id);

        return $form;
    }

    public function viewData(User $user): array
    {
        $activeRoles = UserRoleRequest::where('user_id', $user->id)
            ->where('status', UserRoleRequest::STATUS_ACTIVE)
            ->with('roleCatalog')
            ->get();

        $stacks = [];

        foreach ($activeRoles as $userRole) {
            $roleCatalogId = (int) $userRole->role_catalog_id;

            $primaryForm = $this->formsForRole($roleCatalogId, $user)
                ->where('regulatory_level', 'primary')
                ->first();

            if (empty($primaryForm)) {
                continue;
            }

            $extraForms = $this->formsForRole($roleCatalogId, $user)
                ->whereIn('regulatory_level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
                ->get();

            $submissions = RegulatoryFormSubmission::where('user_id', $user->id)
                ->where('role_catalog_id', $roleCatalogId)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            $primarySubmission = $submissions
                ->where('form_id', $primaryForm->id)
                ->first();

            $extraSubmissions = $submissions
                ->whereIn('level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
                ->groupBy('form_id');

            $stacks[] = [
                'role' => $userRole->roleCatalog,
                'roleRequestStatus' => $userRole->status,
                'primaryForm' => $primaryForm,
                'primarySubmission' => $primarySubmission,
                'extraForms' => $extraForms,
                'extraTemplates' => $extraForms,
                'extraSubmissions' => $extraSubmissions,
            ];
        }

        return $stacks;
    }

    public function storeSubmission(User $user, int $formId, array $fields, string $status, ?int $submissionId = null): RegulatoryFormSubmission
    {
        abort_unless(in_array($status, ['draft', 'pending'], true), 422, 'Invalid submission status.');

        $form = $this->accessibleFormForUser($user, $formId);
        $cleanFields = $this->validateFields($form, $fields, $status === 'pending');
        $previousSubmission = null;

        if ($submissionId) {
            $previousSubmission = RegulatoryFormSubmission::where('id', $submissionId)
                ->where('user_id', $user->id)
                ->where('form_id', $form->id)
                ->where('role_catalog_id', $form->regulatory_role_catalog_id)
                ->firstOrFail();

            $this->assertSubmissionCanBeEdited($previousSubmission);
        }

        $submission = null;

        $activeSubmission = RegulatoryFormSubmission::where('user_id', $user->id)
            ->where('form_id', $form->id)
            ->where('role_catalog_id', $form->regulatory_role_catalog_id)
            ->whereIn('status', self::FINAL_OR_REVIEW_STATUSES)
            ->latest('id')
            ->first();

        if ($activeSubmission) {
            throw ValidationException::withMessages([
                'form_id' => ['This form is already pending or approved. You can resubmit only after rejection.'],
            ]);
        }

        if ($previousSubmission && $previousSubmission->status === 'draft') {
            $submission = $previousSubmission;
        } elseif ($status === 'draft' && empty($previousSubmission)) {
            $submission = RegulatoryFormSubmission::where('user_id', $user->id)
                ->where('form_id', $form->id)
                ->where('role_catalog_id', $form->regulatory_role_catalog_id)
                ->where('status', 'draft')
                ->latest('id')
                ->first();
        }

        if (empty($submission)) {
            $submission = new RegulatoryFormSubmission();
            $submission->user_id = $user->id;
            $submission->previous_submission_id = $previousSubmission?->id;
        }

        $submission->role_catalog_id = $form->regulatory_role_catalog_id;
        $submission->form_id = $form->id;
        $submission->level = $form->regulatory_level;
        $submission->data = $cleanFields;
        $submission->status = $status;
        $submission->rejection_reason = null;
        $submission->reviewed_by = null;
        $submission->reviewed_at = null;
        $submission->save();

        return $submission;
    }

    public function submissionLocksForm(?RegulatoryFormSubmission $submission): bool
    {
        return $submission && in_array($submission->status, self::FINAL_OR_REVIEW_STATUSES, true);
    }

    public function validateStoredSubmissionData(RegulatoryFormSubmission $submission): array
    {
        $form = Form::where('id', $submission->form_id)
            ->with(['fields.options'])
            ->firstOrFail();

        return $this->validateFields($form, (array) $submission->data, true);
    }

    private function formsForRole(int $roleCatalogId, ?User $user = null)
    {
        $query = Form::where('connect_regulatory', true)
            ->where('regulatory_role_catalog_id', $roleCatalogId)
            ->where('enable', true)
            ->whereIn('regulatory_level', self::LEVELS)
            ->with(['fields.options' => function ($query) {
                $query->orderBy('order', 'asc');
            }]);

        if ($user) {
            $query->where($this->countryEligibilityQuery($user));
        }

        return $query;
    }

    private function assertSubmissionCanBeEdited(RegulatoryFormSubmission $submission): void
    {
        if ($this->submissionLocksForm($submission)) {
            throw ValidationException::withMessages([
                'submission_id' => ['This submission is already pending or approved. You can resubmit only after rejection.'],
            ]);
        }
    }

    private function countryEligibilityQuery(User $user): \Closure
    {
        $countries = $this->userCountryCandidates($user);

        return function ($query) use ($countries) {
            $query->whereNull('regulatory_countries')
                ->orWhere('regulatory_countries', '[]');

            foreach ($countries as $country) {
                $query->orWhereJsonContains('regulatory_countries', $country);
            }
        };
    }

    private function userCountryCandidates(User $user): array
    {
        $countries = array_filter([
            $user->country ?? null,
            optional($user->country_id ? \App\Models\Region::find($user->country_id) : null)->title,
        ]);

        return array_values(array_unique(array_map('trim', $countries)));
    }

    private function validateFields(Form $form, array $fields, bool $requireRequiredFields): array
    {
        $formFields = $form->fields->keyBy('id');
        $clean = [];
        $errors = [];

        foreach ($fields as $fieldKey => $value) {
            if (!preg_match('/^field_(\d+)$/', (string) $fieldKey, $matches)) {
                $errors[$fieldKey] = ['Invalid field.'];
                continue;
            }

            $fieldId = (int) $matches[1];
            $field = $formFields->get($fieldId);

            if (empty($field)) {
                $errors[$fieldKey] = ['This field does not belong to this form.'];
                continue;
            }

            $clean[$fieldKey] = $this->normalizeFieldValue($field, $value, $fieldKey, $errors);
        }

        if ($requireRequiredFields) {
            foreach ($form->fields as $field) {
                if (!$field->required) {
                    continue;
                }

                $key = 'field_' . $field->id;
                $value = $clean[$key] ?? null;
                $isEmpty = is_array($value) ? empty($value) : ($value === null || $value === '');

                if ($isEmpty) {
                    $errors[$key] = ['This field is required.'];
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return array_filter($clean, fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

    private function normalizeFieldValue($field, $value, string $fieldKey, array &$errors)
    {
        $optionIds = $field->options instanceof Collection
            ? $field->options->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];

        if ($field->type === 'checkbox') {
            $values = is_array($value) ? $value : (empty($value) ? [] : [$value]);
            $values = array_values(array_unique(array_map('strval', $values)));

            foreach ($values as $submittedOptionId) {
                if (!in_array($submittedOptionId, $optionIds, true)) {
                    $errors[$fieldKey] = ['Invalid selected option.'];
                    break;
                }
            }

            return $values;
        }

        if (in_array($field->type, ['dropdown', 'radio'], true)) {
            if ($value === null || $value === '') {
                return '';
            }

            if (!in_array((string) $value, $optionIds, true)) {
                $errors[$fieldKey] = ['Invalid selected option.'];
            }

            return (string) $value;
        }

        if ($field->type === 'toggle') {
            return !empty($value) ? '1' : '';
        }

        if ($field->type === 'number' && $value !== null && $value !== '' && !is_numeric($value)) {
            $errors[$fieldKey] = ['This field must be a number.'];
        }

        return is_array($value) ? json_encode($value) : $value;
    }
}
