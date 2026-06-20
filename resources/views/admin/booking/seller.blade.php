
@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
	<section class="section">
		<div class="section-header">
			<h1>{{ $pageTitle ?? trans('update.booking_sellers') }}</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
				<div class="breadcrumb-item">{{ $pageTitle ?? trans('update.booking_sellers') }}</div>
			</div>
		</div>

		<div class="section-body">
			<section class="card">
				<div class="card-body">
					<form method="get" class="mb-0">
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label class="input-label">{{ trans('admin/main.search') }}</label>
									<input name="full_name" type="text" class="form-control" value="{{ request()->get('full_name') }}">
								</div>
							</div>

							<div class="col-md-4 d-flex align-items-center ">
								<button type="submit" class="btn btn-primary btn-block btn-lg">{{ trans('admin/main.show_results') }}</button>
							</div>

						</div>
					</form>
				</div>
			</section>

			<div class="row">
				<div class="col-12 col-md-12">
					<div class="card">
						<div class="card-body">
							<div class="table-responsive">
								<table class="table custom-table font-14">
									<tr>
										<th>{{ trans('admin/main.id') }}</th>
										<th class="text-left">{{ trans('admin/main.name') }}</th>
										<th>{{ trans('update.total_bookings') }}</th>
										<th class="text-left">{{ trans('update.categories_and_bookings') }}</th>
										<th width="120">{{ trans('admin/main.actions') }}</th>
									</tr>

									@foreach($users as $user)
										<tr>
											<td>{{ $user->id }}</td>
											<td class="text-left">
												<div class="d-flex align-items-center">
													<div class="ml-3">
														<div class="font-weight-bold">{{ $user->full_name }}</div>
														<div class="text-gray-500 text-small">{{ $user->email ?? '-' }}</div>
													</div>
												</div>
											</td>

											<td>
												{{ !empty($user->bookings) ? $user->bookings->count() : 0 }}
											</td>

											<td class="text-left">
												@php
													$bookings = $user->bookings ?? collect();
													// Group by parent category title if present, otherwise by category title
													$grouped = $bookings->groupBy(function($b) {
														$parent = optional($b->category)->parent;
														if (!empty($parent) and !empty($parent->title)) {
															return $parent->title;
														}
														return optional($b->category)->title ?? trans('update.uncategorized');
													});
													$counter = 1;
												@endphp

												@if($bookings->count())
													@foreach($grouped as $parentTitle => $items)
														<div class="mb-2">
															<strong>{{ $parentTitle }}</strong>
															<ol class="mb-0 pl-3">
																@foreach($items as $booking)
																	<li style="list-style: decimal; margin-left:8px;">
																		<a href="{{ !empty($booking->getUrl()) ? $booking->getUrl() : '#' }}" target="_blank">{{ $counter }}. {{ $booking->title ?? trans('update.deleted_item') }}</a>
																	</li>
																	@php $counter++; @endphp
																@endforeach
															</ol>
														</div>
													@endforeach
												@else
													<div class="text-gray-500">{{ trans('admin/main.no_result') }}</div>
												@endif
											</td>

											<td>
												<div class="d-flex align-items-center gap-8">
													<a href="{{ getAdminPanelUrl('/users/'.$user->id.'/edit') }}" class="btn btn-sm btn-primary">{{ trans('admin/main.edit') }}</a>
													<a href="{{ getAdminPanelUrl('/booking?seller_ids[]='.$user->id) }}" class="btn btn-sm btn-outline-primary">{{ trans('update.view_bookings') }}</a>
												</div>
											</td>
										</tr>
									@endforeach

								</table>
							</div>
						</div>

						<div class="card-footer text-center">
							{{ $users->appends(request()->input())->links() }}
						</div>

					</div>
				</div>
			</div>
		</div>
	</section>
@endsection

@push('scripts_bottom')

@endpush
