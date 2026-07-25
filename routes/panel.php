<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Panel Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace' => 'Panel', 'prefix' => 'panel', 'middleware' => ['impersonate', 'panel', 'share', 'check_maintenance', 'check_restriction']], function () {

    /* Dashboard */
    Route::get('/', 'DashboardController@index');

    /* Events Calender */
    Route::group(['prefix' => 'events-calender'], function () {
        Route::get('/', 'EventsCalendarController@index');
        Route::post('/get-by-day', 'EventsCalendarController@getEventsByDay');
    });

    Route::post('/content-delete-request', 'ContentDeleteRequestController@store');

    Route::group(['prefix' => 'bookings'], function () {

        Route::group(['prefix' => 'invitations'], function () {
            Route::get('/', 'Booking\InvitedBookingsController@index')
                ->name('panel.bookings.invitations');
        });

        /*
        |--------------------------------------------------------------------------
        | ORGANIZATION BOOKINGS  (MOVED HERE — must be ABOVE the /{id} wildcard
        | routes below, otherwise Laravel matches "organization-bookings" as
        | {id} on the PUT/DELETE routes and throws "Method Not Allowed".
        |--------------------------------------------------------------------------
        */
        Route::group(['prefix' => 'organization'], function () {
            Route::get('/', 'Booking\OrganizationBookingController@index')
                ->name('panel.bookings.organization');
        });

        /*
        |--------------------------------------------------------------------------
        | BOOKINGS
        |--------------------------------------------------------------------------
        */

        Route::get('/', 'Booking\BookingController@index')
            ->name('panel.bookings.index');

        Route::get('/new', 'Booking\BookingController@create')
            ->name('panel.bookings.create');

        Route::post('/', 'Booking\BookingController@store')
            ->name('panel.bookings.store');

        Route::get('/{id}/edit', 'Booking\BookingController@edit')
            ->name('panel.bookings.edit');

        // Step-wizard variant of edit, same pattern as courses/upcoming_courses/products below.
        // {step} optional => defaults to step 1 inside BookingController@edit, exactly like
        // ProductController::edit($request, $id, $step = 1)
        Route::get('/{id}/step/{step?}', 'Booking\BookingController@edit')
            ->name('panel.bookings.edit.step');

        Route::post('/{id}/update', 'Booking\BookingController@update')
            ->name('panel.bookings.update.post');

        Route::put('/{id}', 'Booking\BookingController@update')
            ->name('panel.bookings.update');

        Route::post('/{id}/delete', 'Booking\BookingController@destroy')
            ->name('panel.bookings.destroy.post');

        Route::delete('/{id}', 'Booking\BookingController@destroy')
            ->name('panel.bookings.destroy');

        /*
        |--------------------------------------------------------------------------
        | BOOKING ENGINES
        |--------------------------------------------------------------------------
        */

        // CHECK AVAILABILITY

        Route::post('/{id}/availability', 'Booking\BookingController@checkAvailability')
            ->name('panel.bookings.availability');

        // CALCULATE PRICE

        Route::post('/{id}/pricing', 'Booking\BookingController@calculatePrice')
            ->name('panel.bookings.pricing');

        // GET AVAILABLE SLOTS

        Route::get('/{id}/slots', 'Booking\BookingController@getSlots')
            ->name('panel.bookings.slots');

        /*
        |--------------------------------------------------------------------------
        | BOOKING SUB-RESOURCES (used inside wizard steps — resources/assets,
        | recurring time slots, faqs, and category specifications)
        |--------------------------------------------------------------------------
        */

        Route::post('/{id}/resources', 'Booking\BookingController@storeResource')
            ->name('panel.bookings.resources.store');

        Route::delete('/resources/{resource}', 'Booking\BookingController@destroyResource')
            ->name('panel.bookings.resources.destroy');

        Route::post('/{id}/time-slots', 'Booking\BookingController@storeTimeSlot')
            ->name('panel.bookings.time_slots.store');

        Route::delete('/time-slots/{timeSlot}', 'Booking\BookingController@destroyTimeSlot')
            ->name('panel.bookings.time_slots.destroy');

       Route::post('/{id}/availability-overrides', 'Booking\BookingController@storeAvailabilityOverride')
    ->name('panel.bookings.availability_overrides.store');

Route::delete('/availability-overrides/{availability}', 'Booking\BookingController@destroyAvailabilityOverride')
    ->name('panel.bookings.availability_overrides.destroy');

        Route::post('/{id}/faqs', 'Booking\BookingController@storeFaq')
            ->name('panel.bookings.faqs.store');

        Route::delete('/faqs/{faq}', 'Booking\BookingController@destroyFaq')
            ->name('panel.bookings.faqs.destroy');

       Route::get('/specifications/{category}', 'Booking\BookingController@getCategorySpecifications')
            ->name('panel.bookings.specifications');

        Route::get('/{id}/qr/regenerate', 'Booking\BookingController@regenerateQr')
            ->name('panel.bookings.qr.regenerate');

        /*
        |--------------------------------------------------------------------------
        | CALENDAR
        |--------------------------------------------------------------------------
        */

        Route::get('/calendar', 'Booking\BookingCalendarController@index')
            ->name('panel.bookings.calendar');

        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */

        Route::get('/orders/my-orders', 'Booking\MyBookingOrderController@index')
            ->name('panel.bookings.orders');

        // calender
        // calender

        /*
        |--------------------------------------------------------------------------
        | COMMENTS
        |--------------------------------------------------------------------------
        */

        Route::get('/comments', 'Booking\BookingCommentController@index')
            ->name('panel.bookings.comments');

        Route::get('/my-comments', 'Booking\MyBookingCommentController@index')
            ->name('panel.bookings.my-comments');

        Route::get('/my-comments/{id}/delete', 'Booking\MyBookingCommentController@destroy')
            ->name('panel.bookings.my-comments.destroy');

        /*
    |--------------------------------------------------------------------------
    | SALES
    |--------------------------------------------------------------------------
    */

        Route::group(['prefix' => 'sales'], function () {
            Route::get('/', 'Booking\BookingSaleController@index')
                ->name('panel.bookings.sales');

            Route::get('/{id}/bookingOrder/{order_id}/invoice', 'Booking\BookingSaleController@invoice')
                ->name('panel.bookings.sales.invoice');

            Route::get('/{id}/getBookingOrder/{order_id}', 'Booking\BookingSaleController@getBookingOrder')
                ->name('panel.bookings.sales.get-order');

            Route::post('/{id}/bookingOrder/{order_id}/setTrackingCode', 'Booking\BookingSaleController@setTrackingCode')
                ->name('panel.bookings.sales.set-tracking-code');
        });
        /*
        |--------------------------------------------------------------------------
        | MY PURCHASES (Buyer side) — NEW
        |--------------------------------------------------------------------------
        */
        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', 'Booking\MyBookingPurchaseController@index')
                ->name('panel.bookings.purchases');

            Route::get('/{id}/getBookingOrder/{order_id}', 'Booking\MyBookingPurchaseController@getBookingOrder')
                ->name('panel.bookings.purchases.get-order');

            Route::get('/{id}/orderItem/{order_id}/setCompleted', 'Booking\MyBookingPurchaseController@setCompleted')
                ->name('panel.bookings.purchases.set-completed');

            Route::get('/{id}/orderItem/{order_id}/invoice', 'Booking\MyBookingPurchaseController@invoice')
                ->name('panel.bookings.purchases.invoice');
        });

        /*
        |--------------------------------------------------------------------------
        | FAVORITES
        |--------------------------------------------------------------------------
        */

        Route::get('/favorites', 'Booking\BookingFavoriteController@index')
            ->name('panel.bookings.favorites');

        /*
        |--------------------------------------------------------------------------
        | REVIEWS
        |--------------------------------------------------------------------------
        */

        Route::get('/reviews', 'Booking\BookingReviewController@index')
            ->name('panel.bookings.reviews');

    });

    Route::get('/external-connections', 'Booking\CalendarController@index')
    ->name('panel.setting.external-connections');
    // ->middleware('can:calendar.configure');

Route::post('/external-connections/{provider}/credentials', 'Booking\CalendarController@saveCredentials')
    ->name('panel.setting.external-connections.credentials')
    ->where('provider', 'google|outlook');
    // ->middleware('can:calendar.connect');

// 👇 YEH NAYA ROUTE ADD KAREIN
Route::post('/external-connections/{provider}/credentials/connect', 'Booking\CalendarController@saveCredentialsAndConnect')
    ->name('panel.setting.external-connections.credentials.connect')
    ->where('provider', 'google|outlook');
    // ->middleware('can:calendar.connect');

Route::post('/external-connections/{provider}/settings', 'Booking\CalendarController@saveSettings')
    ->name('panel.setting.external-connections.settings')
    ->where('provider', 'google|outlook');
    // ->middleware('can:calendar.configure');

Route::post('/external-connections/ical/toggle', 'Booking\CalendarController@toggleIcal')
    ->name('panel.setting.external-connections.ical.toggle');
    // ->middleware('can:calendar.configure');

Route::post('/external-connections/ical/regenerate', 'Booking\CalendarController@regenerateIcal')
    ->name('panel.setting.external-connections.ical.regenerate');
    // ->middleware('can:calendar.configure');
    Route::get('/calendar/google/callback', 'Booking\CalendarController@googleCallback')
    ->name('calendar.google.callback');

Route::get('/calendar/outlook/callback', 'Booking\CalendarController@outlookCallback')
    ->name('calendar.outlook.callback');
    // panel.php ke andar (external-connections group ke sath) — blade JS
// isi URL par POST karta hai lekin route defined nahi tha:
Route::post('/calendar/{provider}/disconnect', 'Booking\CalendarController@disconnect')
    ->name('panel.setting.external-connections.disconnect')
    ->where('provider', 'google|outlook');

    Route::group(['prefix' => 'users'], function () {
        Route::post('/offlineToggle', 'UserController@offlineToggle');
        Route::get('/{id}/getInfo', 'UserController@getUserInfo');


        Route::get('/login-history/{session_id}/end-session', 'UserLoginHistoryController@endSession');
    });

    Route::group(['prefix' => 'courses'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {

            Route::get('/', 'MyCoursesController@index');
            Route::get('/invitations', 'MyCoursesController@invitations');

            Route::get('/new', 'WebinarController@create');
            Route::post('/store', 'WebinarController@store');
            Route::get('/{id}/step/{step?}', 'WebinarController@edit');
            Route::get('/{id}/edit', 'WebinarController@edit')->name('panel_edit_webinar');
            Route::post('/{id}/update', 'WebinarController@update');
            Route::get('/{id}/delete', 'WebinarController@destroy');
            Route::get('/{id}/duplicate', 'WebinarController@duplicate');
            Route::get('/{id}/export-students-list', 'WebinarController@exportStudentsList');
            Route::post('/order-items', 'WebinarController@orderItems');
            Route::post('/{id}/getContentItemByLocale', 'WebinarController@getContentItemByLocale');

            Route::group(['prefix' => '{course_id}/statistics'], function () {
                Route::get('/', 'WebinarStatisticController@index');
            });

          Route::group(['prefix' => '{course_id}/media'], function () {
                Route::get('/delete-icon', 'WebinarController@deleteIcon');
            });

            Route::get('/{id}/qr/regenerate', 'WebinarController@regenerateQr');
        });
        Route::get('/organization_classes', 'MyOrganizationCoursesController@index');

        Route::get('/{id}/sale/{sale_id}/invoice', 'WebinarController@invoice');
        Route::get('/{id}/getNextSessionInfo', 'WebinarController@getNextSessionInfo');

        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', 'MyPurchasedCoursesController@index');
            Route::post('/getJoinInfo', 'MyPurchasedCoursesController@getJoinInfo');
        });

        Route::post('/search', 'WebinarController@search');

        Route::group(['prefix' => 'comments'], function () {
            Route::get('/', 'MyCourseCommentsController@index');
            Route::post('/store', 'MyCourseCommentsController@store');
            Route::post('/{id}/update', 'MyCourseCommentsController@update');
            Route::get('/{id}/delete', 'MyCourseCommentsController@destroy');
            Route::post('/{id}/reply', 'MyCourseCommentsController@reply');
            Route::post('/{id}/report', 'MyCourseCommentsController@report');
        });

        Route::get('/my-comments', 'MyCommentsController@index');

        Route::group(['prefix' => 'favorites'], function () {
            Route::get('/', 'FavoriteController@index');
            Route::get('/{id}/delete', 'FavoriteController@destroy');
        });

        Route::group(['prefix' => 'personal-notes'], function () {
            Route::get('/', 'CoursePersonalNotesController@index');
            Route::get('/{id}/delete', 'CoursePersonalNotesController@delete');
        });

        /* Attendances */
        Route::group(['prefix' => 'attendances', 'middleware' => 'user.not.access'], function () {
            Route::get('/', 'AttendancesController@index');
            Route::get('/{session_id}/details', 'AttendanceDetailsController@index');
            Route::get('/{session_id}/details/{student_id}/status/{status}', 'AttendanceDetailsController@changeStatus');
        });

        /* My Attendances */
        Route::group(['prefix' => 'my-attendances'], function () {
            Route::get('/', 'MyAttendancesController@index');
        });
    });

    Route::group(['prefix' => 'upcoming_courses'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {
            Route::get('/', 'UpcomingCoursesController@index');
            Route::get('/new', 'UpcomingCoursesController@create');
            Route::post('/store', 'UpcomingCoursesController@store');
            Route::get('/{id}/step/{step?}', 'UpcomingCoursesController@edit');
            Route::get('/{id}/edit', 'UpcomingCoursesController@edit');
            Route::post('/{id}/update', 'UpcomingCoursesController@update');
            Route::get('/{id}/delete', 'UpcomingCoursesController@destroy');
            Route::post('/order-items', 'UpcomingCoursesController@orderItems');
            Route::post('/{id}/getContentItemByLocale', 'UpcomingCoursesController@getContentItemByLocale');

            Route::get('/{id}/assign-course', 'UpcomingCoursesController@assignCourseModal');
            Route::post('/{id}/assign-course', 'UpcomingCoursesController@storeAssignCourse');
            Route::get('/{id}/followers', 'UpcomingCourseFollowersController@index');
        });

        Route::get('/followings', 'UpcomingCourseFollowingsController@index');
        Route::get('/followings/{upcoming_id}/delete', 'UpcomingCourseFollowingsController@deleteFollowing');
    });

    Route::group(['prefix' => 'quizzes'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {

            Route::get('/', 'QuizController@index');
            Route::get('/new', 'QuizController@create');
            Route::post('/store', 'QuizController@store');
            Route::get('/{id}/edit', 'QuizController@edit')->name('panel_edit_quiz');
            Route::post('/{id}/update', 'QuizController@update');
            Route::get('/{id}/delete', 'QuizController@destroy');
            Route::post('/{id}/order-items', 'QuizController@orderItems');
        });

        Route::get('/{id}/overview', 'QuizController@overview');
        Route::get('/{id}/start', 'QuizController@start');
        Route::post('/{id}/store-result', 'QuizController@quizzesStoreResult');
        Route::get('/{quizResultId}/status', 'QuizController@status')->name('quiz_status');

        Route::get('/opens', 'OpenQuizzesController@index');
        Route::get('/my-results', 'QuizMyResultsController@index');


        Route::group(['prefix' => 'results'], function () {
            Route::get('/', 'QuizResultsController@index');
            Route::get('/{quizResultId}/details', 'QuizResultsController@show');
            Route::get('/{quizResultId}/edit', 'QuizResultsController@edit');
            Route::post('/{quizResultId}/update', 'QuizResultsController@update');
            Route::get('/{quizResultId}/delete', 'QuizResultsController@delete');
            Route::get('/{quizResultId}/showCertificate', 'QuizResultsController@makeCertificate');
        });


    });

    Route::group(['prefix' => 'quizzes-questions'], function () {
        Route::get('/get-form', 'QuizQuestionController@getForm');
        Route::post('/store', 'QuizQuestionController@store');
        Route::get('/{id}/edit', 'QuizQuestionController@edit');
        Route::get('/{id}/getQuestionByLocale', 'QuizQuestionController@getQuestionByLocale');
        Route::post('/{id}/update', 'QuizQuestionController@update');
        Route::get('/{id}/delete', 'QuizQuestionController@destroy');
    });

    Route::group(['prefix' => 'filters'], function () {
        Route::get('/get-by-category-id/{categoryId}', 'FilterController@getByCategoryId');
    });

    Route::group(['prefix' => 'tickets'], function () {
        Route::post('/store', 'TicketController@store');
        Route::post('/{id}/update', 'TicketController@update');
        Route::get('/{id}/delete', 'TicketController@destroy');
    });

    Route::group(['prefix' => 'sessions'], function () {
        Route::post('/store', 'SessionController@store');
        Route::post('/{id}/update', 'SessionController@update');
        Route::get('/{id}/delete', 'SessionController@destroy');

        Route::get('/{id}/endAgora', 'SessionController@endAgora');
        Route::get('/{id}/toggleUsersJoinToAgora', 'SessionController@toggleUsersJoinToAgora');


        /* Join */
        Route::group(['prefix' => '/{id}/join'], function () {
            Route::get('/', 'SessionController@joinToSession');
            Route::get('/toBigBlueButton', 'SessionController@joinToBigBlueButton');
            Route::get('/toAgora', 'SessionController@joinToAgora');
            Route::get('/toJitsi', 'SessionController@joinToJitsi');
        });
    });

    Route::group(['prefix' => 'chapters'], function () {
        Route::get('/get-form', 'ChapterController@getForm');
        Route::get('/{id}', 'ChapterController@getChapter');
        Route::get('/getAllByWebinarId/{webinar_id}', 'ChapterController@getAllByWebinarId');
        Route::post('/store', 'ChapterController@store');
        Route::get('/{id}/edit', 'ChapterController@edit');
        Route::post('/{id}/update', 'ChapterController@update');
        Route::get('/{id}/delete', 'ChapterController@destroy');
        Route::post('/change', 'ChapterController@change');
    });

    Route::group(['prefix' => 'files'], function () {
        Route::post('/store', 'FileController@store');
        Route::post('/{id}/update', 'FileController@update');
        Route::get('/{id}/delete', 'FileController@destroy');
    });

    Route::group(['prefix' => 'assignments'], function () {
        Route::post('/store', 'AssignmentController@store');
        Route::post('/{id}/update', 'AssignmentController@update');
        Route::get('/{id}/delete', 'AssignmentController@destroy');

        Route::get('/my-requests', 'AssignmentController@myAssignments');
        Route::get('/', 'AssignmentController@myCoursesAssignments');
        Route::get('/histories', 'AssignmentController@myCoursesAssignmentsAllHistories');
        Route::get('/{id}/students', 'AssignmentController@students');
    });

    Route::group(['prefix' => 'text-lesson'], function () {
        Route::post('/store', 'TextLessonsController@store');
        Route::post('/{id}/update', 'TextLessonsController@update');
        Route::get('/{id}/delete', 'TextLessonsController@destroy');
    });

    Route::group(['prefix' => 'prerequisites'], function () {
        Route::post('/store', 'PrerequisiteController@store');
        Route::post('/{id}/update', 'PrerequisiteController@update');
        Route::get('/{id}/delete', 'PrerequisiteController@destroy');
    });

    Route::group(['prefix' => 'relatedCourses'], function () {
        Route::post('/store', 'RelatedCoursesController@store');
        Route::post('/{id}/update', 'RelatedCoursesController@update');
        Route::get('/{id}/delete', 'RelatedCoursesController@destroy');
    });

    Route::group(['prefix' => 'relatedProducts'], function () {
        Route::post('/store', 'RelatedProductsController@store');
        Route::post('/{id}/update', 'RelatedProductsController@update');
        Route::get('/{id}/delete', 'RelatedProductsController@destroy');
    });

    Route::group(['prefix' => 'faqs'], function () {
        Route::post('/store', 'FAQController@store');
        Route::post('/{id}/update', 'FAQController@update');
        Route::get('/{id}/delete', 'FAQController@destroy');
    });

    Route::group(['prefix' => 'webinar-extra-description'], function () {
        Route::post('/store', 'WebinarExtraDescriptionController@store');
        Route::post('/{id}/update', 'WebinarExtraDescriptionController@update');
        Route::get('/{id}/delete', 'WebinarExtraDescriptionController@destroy');
    });

    Route::group(['prefix' => 'webinar-quiz'], function () {
        Route::post('/store', 'WebinarQuizController@store');
        Route::post('/{id}/update', 'WebinarQuizController@update');
        Route::get('/{id}/delete', 'WebinarQuizController@destroy');
    });


    Route::group(['prefix' => 'certificates'], function () {
        Route::get('/', 'CertificatesListsController@index');
        Route::get('/students', 'GeneratedCertificatesController@index');
        Route::get('/students/{certificateId}/show', 'GeneratedCertificatesController@download');
        Route::get('/{type}/{typeItemId}/details', 'GeneratedCertificatesController@index')->where('type', 'quiz|courses|bundles');

        /* My */
        Route::group(['prefix' => 'my-achievements'], function () {
            Route::get('/', 'MyCertificatesController@index');
            Route::get('/{certificateId}/show', 'MyCertificatesController@download');
        });
    });

    Route::group(['prefix' => 'meetings'], function () {
        Route::get('/reservation', 'ReserveMeetingController@reservation');
        Route::get('/requests', 'ReserveMeetingController@requests');

        Route::get('/settings', 'MeetingController@setting')->name('meeting_setting');
        Route::get('/get-meeting-time-modal', 'MeetingController@getMeetingTimeModal');
        Route::post('/{id}/update', 'MeetingController@update');
        Route::post('saveTime', 'MeetingController@saveTime');
        Route::post('deleteTime', 'MeetingController@deleteTime');
        Route::post('temporaryDisableMeetings', 'MeetingController@temporaryDisableMeetings');


        Route::get('/{id}/join-modal', 'ReserveMeetingController@getJoinModal');
        Route::get('/{id}/join', 'ReserveMeetingController@join');
        Route::post('/create-link', 'ReserveMeetingController@createLink');
        Route::get('/{id}/get-finish-modal', 'ReserveMeetingController@getFinishModal');
        Route::post('/{id}/finish', 'ReserveMeetingController@finish');

        Route::get('/{id}/create-session', 'ReserveMeetingController@getCreateSessionModal');
        Route::post('/{id}/create-session', 'ReserveMeetingController@createSession');

        Route::get('/{id}/contact-info', 'ReserveMeetingController@getContactInfoModal');

        /* Meeting Packages*/
        Route::group(['prefix' => 'packages'], function () {
            Route::get('/', 'MeetingPackagesController@index');
            Route::post('/store', 'MeetingPackagesController@store');
            Route::get('/{id}/edit', 'MeetingPackagesController@edit');
            Route::post('/{id}/update', 'MeetingPackagesController@update');
            Route::get('/{id}/delete', 'MeetingPackagesController@delete');
        });

        /* Sold Packages */
        Route::group(['prefix' => 'sold-packages'], function () {
            Route::get('/', 'SoldMeetingPackagesController@index');
            Route::get('/{id}/get-student-detail', 'SoldMeetingPackagesController@getStudentDetail');

            /* Sessions */
            Route::group(['prefix' => '{id}/sessions'], function () {
                Route::get('/', 'SoldMeetingPackageSessionsController@index');

                /* Date */
                Route::get('/{session_id}/set-date', 'SoldMeetingPackageSessionsController@getSessionDateForm');
                Route::post('/{session_id}/set-date', 'SoldMeetingPackageSessionsController@updateSessionDate');

                /* API */
                Route::get('/{session_id}/set-api', 'SoldMeetingPackageSessionsController@getSessionApiForm');
                Route::post('/{session_id}/set-api', 'SoldMeetingPackageSessionsController@updateSessionApi');

                /* Join */
                Route::get('/{session_id}/join-modal', 'SoldMeetingPackageSessionsController@joinToSessionModal');
                Route::get('/{session_id}/join-to-session', 'SoldMeetingPackageSessionsController@joinToSession');

                /* Finish */
                Route::get('/{session_id}/finish-modal', 'SoldMeetingPackageSessionsController@finishSessionModal');
                Route::get('/{session_id}/finish', 'SoldMeetingPackageSessionsController@finishSession');
            });
        });

        /* Purchased Packages */
        Route::group(['prefix' => 'purchased-packages'], function () {
            Route::get('/', 'PurchasedMeetingPackagesController@index');
            Route::get('/{id}/get-instructor-detail', 'PurchasedMeetingPackagesController@getInstructorDetail');

            /* Sessions */
            Route::group(['prefix' => '{id}/sessions'], function () {
                Route::get('/', 'PurchasedMeetingPackageSessionsController@index');

                /* Join */
                Route::get('/{session_id}/join-modal', 'PurchasedMeetingPackageSessionsController@joinToSessionModal');
                Route::get('/{session_id}/join-to-session', 'PurchasedMeetingPackageSessionsController@joinToSession');

                /* Finish */
                Route::get('/{session_id}/finish-modal', 'PurchasedMeetingPackageSessionsController@finishSessionModal');
                Route::get('/{session_id}/finish', 'PurchasedMeetingPackageSessionsController@finishSession');
            });
        });


    });
 

    Route::group(['prefix' => 'financial'], function () {
        Route::get('/sales', 'SaleController@index');
        Route::get('/summary', 'AccountingSummaryController@index');

        Route::get('/account', 'AccountingController@account');
        Route::post('/charge', 'AccountingController@charge');

        /* Payout */
        Route::group(['prefix' => 'payout'], function () {
            Route::get('/', 'PayoutController@index');
            Route::post('/request', 'PayoutController@requestPayout');
            Route::get('/{id}/details', 'PayoutController@getDetails');
        });

        Route::group(['prefix' => 'offline-payments'], function () {
            Route::get('/{id}/edit', 'AccountingController@account');
            Route::post('/{id}/update', 'AccountingController@updateOfflinePayment');
            Route::get('/{id}/delete', 'AccountingController@deleteOfflinePayment');
        });

        Route::group(['prefix' => 'subscribes'], function () {
            Route::get('/', 'SubscribesController@index');
            Route::get('/{id}/installments', 'SubscribesController@getInstallmentsBySubscribe');
        });
        Route::post('/pay-subscribes', 'SubscribesController@pay');

        Route::group(['prefix' => 'registration-packages'], function () {
            Route::get('/', 'RegistrationPackagesController@index')->name('panelRegistrationPackagesLists');
            Route::get('/{id}/installments', 'RegistrationPackagesController@getInstallmentsByRegistrationPackage');
            Route::post('/pay-registration-packages', 'RegistrationPackagesController@pay')->name('payRegistrationPackage');
        });

        Route::group(['prefix' => 'installments'], function () {
            Route::get('/', 'InstallmentsController@index');
            Route::get('/{id}/details', 'InstallmentsController@show');
            Route::get('/{id}/cancel', 'InstallmentsController@cancelVerification');
            Route::get('/{id}/pay_upcoming_part', 'InstallmentsController@payUpcomingPart');
            Route::get('/{id}/steps/{step_id}/pay', 'InstallmentsController@payStep');
        });
    });

    Route::group(['prefix' => 'setting'], function () {
        Route::get('/step/{step?}', 'UserController@setting');
        Route::get('/', 'UserController@setting');
        Route::post('/', 'UserController@update');
        Route::post('/metas', 'UserController@storeMetas');
        Route::post('metas/{meta_id}/update', 'UserController@updateMeta');
        Route::get('metas/{meta_id}/delete', 'UserController@deleteMeta');
        Route::get('/deleteAccount', 'UserController@deleteAccount');
        Route::get('/media/{type}/delete', 'UserController@deleteUserMedia');

        Route::group(['prefix' => '/attachments'], function () {
            Route::get('/get-form', 'UserProfileAttachmentsController@getForm');
            Route::post("/store", 'UserProfileAttachmentsController@store');
            Route::get("/{id}/edit", 'UserProfileAttachmentsController@edit');
            Route::post("/{id}/update", 'UserProfileAttachmentsController@update');
            Route::get("/{id}/delete", 'UserProfileAttachmentsController@delete');
        });

        // ═══════════════════════════════════════════════════════════════
        // CHECKOUT SETTINGS ROUTES
        // ═══════════════════════════════════════════════════════════════
        Route::get('/checkout-options', 'CheckoutSettingsController@index')
            ->name('panel.checkout-settings');
        Route::post('/checkout-settings/save', 'CheckoutSettingsController@save')
            ->name('panel.checkout-settings.save');
        Route::post('/checkout-settings/entity', 'CheckoutSettingsController@saveEntityModules')
            ->name('panel.checkout-settings.entity');


              // ═══════════════════════════════════════════════════════════════
    // ABILITIES (vendor-side enable/disable/configure)
    // ═══════════════════════════════════════════════════════════════
    Route::post('/abilities/{abilityId}/save', 'VendorAbilityController@saveConfig')
        ->name('panel.setting.abilities.save');

    Route::post('/abilities/{abilityId}/disable', 'VendorAbilityController@disable')
        ->name('panel.setting.abilities.disable');
    });

    Route::group(['prefix' => 'setting/erp'], function () {
    Route::post('/{type}/save', 'ErpSettingsController@save')
        ->where('type', 'import_export|dropshipping')
        ->name('panel.setting.erp.save');

    Route::post('/{type}/regenerate-key', 'ErpSettingsController@regenerateKey')
        ->where('type', 'import_export|dropshipping')
        ->name('panel.setting.erp.regenerate_key');

    Route::post('/{type}/toggle-subscription', 'ErpSettingsController@toggleSubscription')
        ->where('type', 'import_export|dropshipping')
        ->name('panel.setting.erp.toggle_subscription');
});
    Route::group(['prefix' => 'support'], function () {
        Route::get('/', 'SupportsController@index');
        Route::get('/new', 'SupportsController@create');
        Route::post('/store', 'SupportsController@store');
        Route::get('{id}/conversations', 'SupportsController@index');
        Route::post('{id}/conversations', 'SupportsController@storeConversations');
        Route::get('{id}/close', 'SupportsController@close');

        Route::group(['prefix' => 'tickets'], function () {
            Route::get('/', 'SupportsController@tickets');
            Route::get('{id}/conversations', 'SupportsController@tickets');
        });
    });

    Route::group(['prefix' => 'marketing', 'middleware' => 'user.not.access'], function () {

        /* Special Offers */
        Route::group(['prefix' => 'special_offers'], function () {
            Route::get('/', 'SpecialOfferController@index')->name('special_offer_index');
            Route::post('/store', 'SpecialOfferController@store');
            Route::get('/{id}/disable', 'SpecialOfferController@disable');
        });

        /* Promotions */
        Route::group(['prefix' => 'promotions'], function () {
            Route::get('/', 'PromotionsController@index');
            Route::get('/{id}/pay-form', 'PromotionsController@getPayForm');
            Route::post('/{id}/pay', 'PromotionsController@payPromotion');
        });
    });

    Route::group(['prefix' => 'marketing'], function () {
        Route::get('/affiliates', 'AffiliateController@index');

        /* Registration Bonus */
        Route::get('/registration_bonus', 'RegistrationBonusController@index');

        /* Discounts */
        Route::group(['prefix' => 'discounts'], function () {
            Route::get('/', 'DiscountController@index');
            Route::get('/new', 'DiscountController@create');
            Route::post('/store', 'DiscountController@store');
            Route::get('/{id}/edit', 'DiscountController@edit');
            Route::post('/{id}/update', 'DiscountController@update');
            Route::get('/{id}/delete', 'DiscountController@delete');
        });

    });

    Route::group(['prefix' => 'noticeboard'], function () {
        Route::get('/', 'NoticeboardController@index');
        Route::get('/new', 'NoticeboardController@create');
        Route::post('/store', 'NoticeboardController@store');
        Route::get('/{noticeboard_id}/edit', 'NoticeboardController@edit');
        Route::post('/{noticeboard_id}/update', 'NoticeboardController@update');
        Route::get('/{noticeboard_id}/delete', 'NoticeboardController@delete');
        Route::get('/{noticeboard_id}/saveStatus', 'NoticeboardController@saveStatus');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationsController@index');
        Route::get('/{id}/saveStatus', 'NotificationsController@saveStatus');
        Route::get('/mark-all-as-read', 'NotificationsController@markAllAsRead');
    });

    // organization instructor and students route
    Route::group(['prefix' => 'manage'], function () {
        Route::get('/{user_type}', 'OrganManageUsersController@manageUsers');
        Route::get('/{user_type}/new', 'OrganManageUsersController@createUser');
        Route::post('/{user_type}/new', 'OrganManageUsersController@storeUser');
        Route::get('/{user_type}/{user_id}/edit', 'OrganManageUsersController@editUser');
        Route::get('/{user_type}/{user_id}/edit/step/{step?}', 'OrganManageUsersController@editUser');
        Route::get('/{user_type}/{user_id}/delete', 'OrganManageUsersController@deleteUser');
    });

    Route::group(['prefix' => 'rewards'], function () {
        Route::get('/', 'RewardController@index');
        Route::post('/exchange', 'RewardController@exchange');
    });

    Route::group(['prefix' => 'store', 'namespace' => 'Store'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {

            Route::group(['prefix' => 'products'], function () {
                Route::get('/', 'ProductController@index');
                Route::get('/new', 'ProductController@create');
                Route::post('/store', 'ProductController@store');
                Route::get('/{id}/step/{step?}', 'ProductController@edit');
                Route::get('/{id}/edit', 'ProductController@edit');
                Route::post('/{id}/update', 'ProductController@update');
                Route::get('/{id}/delete', 'ProductController@destroy');
                Route::get('/{id}/media/{mediaId}/delete', 'ProductController@deleteMediaById');
             Route::post('/{id}/getContentItemByLocale', 'ProductController@getContentItemByLocale');
                Route::post('/search', 'ProductController@search');
                Route::get('/{id}/qr/regenerate', 'ProductController@regenerateQr');

                Route::group(['prefix' => 'filters'], function () {
                    Route::get('/get-by-category-id/{categoryId}', 'ProductFilterController@getByCategoryId');
                });

                Route::group(['prefix' => 'specifications'], function () {
                    Route::get('/{id}/get', 'ProductSpecificationController@getItem');
                    Route::post('/store', 'ProductSpecificationController@store');
                    Route::post('/{id}/update', 'ProductSpecificationController@update');
                    Route::get('/{id}/delete', 'ProductSpecificationController@destroy');
                    Route::post('/order-items', 'ProductSpecificationController@orderItems');
                    Route::post('/search', 'ProductSpecificationController@search');
                    Route::get('/get-by-category-id/{categoryId}', 'ProductSpecificationController@getByCategoryId');
                });

                Route::group(['prefix' => 'files'], function () {
                    Route::post('/store', 'ProductFileController@store');
                    Route::post('/{id}/update', 'ProductFileController@update');
                    Route::get('/{id}/delete', 'ProductFileController@destroy');
                    Route::post('/order-items', 'ProductFileController@orderItems');
                });

                Route::group(['prefix' => 'faqs'], function () {
                    Route::post('/store', 'ProductFaqController@store');
                    Route::post('/{id}/update', 'ProductFaqController@update');
                    Route::get('/{id}/delete', 'ProductFaqController@destroy');
                    Route::post('/order-items', 'ProductFaqController@orderItems');
                });

                Route::group(['prefix' => 'comments'], function () {
                    Route::get('/', 'CommentController@index');
                });
            });

            Route::group(['prefix' => 'sales'], function () {
                Route::get('/', 'SaleController@index');
                Route::get('/{id}/productOrder/{order_id}/invoice', 'SaleController@invoice');
                Route::get('/{id}/getProductOrder/{order_id}', 'SaleController@getProductOrder');
                Route::post('/{id}/productOrder/{order_id}/setTrackingCode', 'SaleController@setTrackingCode');
            });
        });

        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', 'MyPurchaseController@index');
            Route::get('/{id}/getProductOrder/{order_id}', 'MyPurchaseController@getProductOrder');
            Route::get('/{id}/productOrder/{order_id}/setGotTheParcel', 'MyPurchaseController@setGotTheParcel');
            Route::get('/{id}/productOrder/{order_id}/invoice', 'MyPurchaseController@invoice');
        });


        Route::group(['prefix' => 'products'], function () {
            Route::get('/my-comments', 'MyCommentController@index');
            Route::get('/files/{id}/download', 'ProductFileController@download');
            Route::get('/{id}/getFilesModal', 'ProductController@getFilesModal');
        });
    });

 Route::group(['prefix' => 'bundles'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {
            Route::get('/', 'BundlesController@index');
            Route::get('/new', 'BundlesController@create');
            Route::post('/store', 'BundlesController@store');
            Route::get('/{id}/step/{step?}', 'BundlesController@edit');
            Route::get('/{id}/edit', 'BundlesController@edit');
            Route::post('/{id}/update', 'BundlesController@update');
            Route::get('/{id}/delete', 'BundlesController@destroy');
            Route::post('/{id}/getContentItemByLocale', 'BundlesController@getContentItemByLocale');
            Route::get('/{id}/courses', 'BundlesController@courses');
            Route::get('/{id}/export-students-list', 'BundlesController@exportStudentsList');
            Route::get('/{id}/qr/regenerate', 'BundlesController@regenerateQr');
        });
    });

    Route::group(['prefix' => 'bundle-webinars'], function () {
        Route::post('/store', 'BundleWebinarsController@store');
        Route::post('/{id}/update', 'BundleWebinarsController@update');
        Route::get('/{id}/delete', 'BundleWebinarsController@destroy');
    });

    Route::group(['prefix' => 'course-noticeboard'], function () {
        Route::get('/', 'CourseNoticeboardController@index');
        Route::get('/new', 'CourseNoticeboardController@create');
        Route::post('/store', 'CourseNoticeboardController@store');
        Route::get('/{noticeboard_id}/edit', 'CourseNoticeboardController@edit');
        Route::post('/{noticeboard_id}/update', 'CourseNoticeboardController@update');
        Route::get('/{noticeboard_id}/delete', 'CourseNoticeboardController@delete');
        Route::get('/{noticeboard_id}/saveStatus', 'CourseNoticeboardController@saveStatus');
    });

    Route::group(['prefix' => 'forums'], function () {
        Route::get('/topics', 'ForumTopicsController@index');
        Route::get('/topics/{id}/removeBookmarks', 'ForumTopicsController@removeBookmarks');
        Route::get('/posts', 'ForumPostsController@index');

        Route::get('/bookmarks', 'ForumsBookmarksController@index');
    });

    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'BlogPostsController@index');
        Route::get('/new', 'BlogPostsController@create');
        Route::post('/store', 'BlogPostsController@store');
        Route::get('/{post_id}/edit', 'BlogPostsController@edit');
        Route::post('/{post_id}/update', 'BlogPostsController@update');
        Route::get('/{post_id}/delete', 'BlogPostsController@delete');

        Route::group(['prefix' => 'comments'], function () {
            Route::get('/', 'BlogCommentsController@index');
        });

        Route::group(['prefix' => '/{post_id}/related-posts'], function () {
            Route::post('/store', 'BlogRelatedPostsController@store');
            Route::post('/{id}/update', 'BlogRelatedPostsController@update');
            Route::get('/{id}/delete', 'BlogRelatedPostsController@destroy');
        });
    });

    Route::group(['prefix' => 'ai-contents'], function () {
        Route::get('/', 'AiContentController@index');
        Route::post('/generate', 'AiContentController@generate');
    });
    Route::group(['prefix' => 'roles'], function () {
    Route::get('/', 'RoleManagementController@index')->name('panel.roles.index');
    Route::post('/request', 'RoleManagementController@requestRole')->name('panel.roles.request');
});
Route::group(['prefix' => 'regulatory'], function () {
    Route::get('/', 'RegulatoryFormController@show')->name('panel.regulatory.show');
    Route::post('/save-draft', 'RegulatoryFormController@saveDraft')->name('panel.regulatory.draft');
    Route::post('/submit', 'RegulatoryFormController@submitForReview')->name('panel.regulatory.submit');
    Route::post('/add-slot', 'RegulatoryFormController@addSlot')->name('panel.regulatory.add_slot');
    Route::delete('/{submissionId}', 'RegulatoryFormController@destroy')->name('panel.regulatory.destroy');
});

    // Events Routes
    Route::group(['prefix' => 'events', 'middleware' => 'check_event_feature_status'], function () {
        Route::get('/', 'EventsController@index');
        Route::get('/new', 'EventsController@create');
        Route::post('/store', 'EventsController@store');
        Route::get('/{id}/edit', 'EventsController@edit');
        Route::get('/{id}/step/{step}', 'EventsController@edit');
        Route::post('/{id}/update', 'EventsController@update');
        Route::get('/{id}/delete', 'EventsController@delete');

        Route::post('/search', 'EventsController@search');
        Route::post('/{id}/getContentItemByLocale', 'EventsController@getContentItemByLocale');

        // Tickets
        Route::group(['prefix' => '{event_id}/tickets'], function () {
            Route::post('/store', 'EventTicketsController@store');
            Route::post('/{id}/update', 'EventTicketsController@update');
            Route::get('/{id}/delete', 'EventTicketsController@delete');
            Route::post('/order-items', 'EventTicketsController@orderItems');
        });

        // Speakers
        Route::group(['prefix' => '{event_id}/speakers'], function () {
            Route::post('/store', 'EventSpeakersController@store');
            Route::post('/{id}/update', 'EventSpeakersController@update');
            Route::get('/{id}/delete', 'EventSpeakersController@delete');
            Route::post('/order-items', 'EventSpeakersController@orderItems');
        });

        // Create Session
        Route::group(['prefix' => '{event_id}/create-session'], function () {
            Route::get('/', 'EventsSessionController@getSessionModal');
            Route::post('/', 'EventsSessionController@createSession');
        });

        // Join Session
        Route::get('/{event_id}/join-session-modal', 'EventsSessionController@getJoinSessionModal');
        Route::get('/{event_id}/join-session', 'EventsSessionController@joinToSession');

        // Sold Tickets
        Route::group(['prefix' => '{event_id}/sold-tickets'], function () {
            Route::get('/', 'EventSoldTicketsController@index');
            Route::get('/{id}/details', 'EventSoldTicketsController@details');
        });

        // organization_lists
        Route::group(['prefix' => 'my-organization'], function () {
            Route::get('/', 'OrganizationEventsController@index');
        });

        // my_purchases
        Route::group(['prefix' => 'my-purchases'], function () {
            Route::get('/', 'MyPurchaseEventsController@index');
            Route::get('/{event_id}/join-modal', 'MyPurchaseEventsController@joinToSessionModal');
            Route::get('/{event_id}/join-to-session', 'MyPurchaseEventsController@joinToSession');
            Route::get('/{event_id}/invoice', 'MyPurchaseEventsController@invoice');

            Route::group(['prefix' => '{event_id}/tickets'], function () {
                Route::get('/', 'MyPurchaseEventTicketsController@index');
                Route::get('/{id}/details', 'MyPurchaseEventTicketsController@details');
            });
        });

        // comments
        Route::group(['prefix' => 'comments'], function () {
            Route::get('/', 'MyEventsCommentsController@index');
            Route::post('/{id}/update', 'MyEventsCommentsController@update');
            Route::get('/{id}/delete', 'MyEventsCommentsController@destroy');
            Route::post('/{id}/reply', 'MyEventsCommentsController@reply');
            Route::post('/{id}/report', 'MyEventsCommentsController@report');
        });

        // my_comments
        Route::group(['prefix' => 'my-comments'], function () {
            Route::get('/', 'MyCommentsOnEventsController@index');
        });

    });


});