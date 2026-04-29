<style>
    .badge-info { background-color: #3abaf4 !important; }
    .badge-success { background-color: #47c363 !important; }
</style>
<div class="row">
    <div class="col-12 col-md-12">
        <!-- Add this in your webinar create/edit Blade template -->
        <div class="card">
            <div class="mx-4 mt-4">
                <div>
                    <h3>{{ trans('admin/pages/webinars.excel_upload_instructions') }}</h3>
                </div>
                <div class="mt-1">
                    <p>{{ trans('admin/pages/webinars.follow_instructions') }}</p>
                </div>
            </div>
            <div class="card-body">
                <!-- Instructions Table -->
                <table class="table table-striped font-14">
                    <thead>
                        <tr>
                            <th>{{ trans('admin/pages/webinars.column_number') }}</th>
                            <th>{{ trans('admin/pages/webinars.column_name') }}</th>
                            <th>{{ trans('admin/pages/webinars.instruction') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                {{ trans('admin/pages/webinars.type') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.type_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>
                                {{ trans('admin/pages/webinars.locale') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.locale_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>
                                {{ trans('admin/pages/webinars.title') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.title_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>
                                {{ trans('admin/pages/webinars.slug') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.slug_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>
                                {{ trans('admin/pages/webinars.thumbnail') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.thumbnail_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>
                                {{ trans('admin/pages/webinars.image_cover') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.image_cover_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>
                                {{ trans('admin/pages/webinars.description') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.description_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>
                                {{ trans('admin/pages/webinars.teacher_id') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.teacher_id_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>
                                {{ trans('admin/pages/webinars.category_id') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.category_id_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>
                                {{ trans('admin/pages/webinars.duration') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.duration_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>
                                {{ trans('admin/pages/webinars.start_date') }}
                                <span class="badge badge-success">{{ trans('admin/pages/webinars.required_for_webinars') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.start_date_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>
                                {{ trans('admin/pages/webinars.timezone') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.timezone_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>13</td>
                            <td>
                                {{ trans('admin/pages/webinars.capacity') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.capacity_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>
                                {{ trans('admin/pages/webinars.price') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.price_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>15</td>
                            <td>
                                {{ trans('admin/pages/webinars.organization_price') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.organization_price_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>16</td>
                            <td>
                                {{ trans('admin/pages/webinars.video_demo') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.video_demo_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>17</td>
                            <td>
                                {{ trans('admin/pages/webinars.video_demo_source') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.video_demo_source_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>18</td>
                            <td>
                                {{ trans('admin/pages/webinars.sales_count_number') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.sales_count_number_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>19</td>
                            <td>
                                {{ trans('admin/pages/webinars.support') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.support_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>20</td>
                            <td>
                                {{ trans('admin/pages/webinars.certificate') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.certificate_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>21</td>
                            <td>
                                {{ trans('admin/pages/webinars.downloadable') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.downloadable_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>22</td>
                            <td>
                                {{ trans('admin/pages/webinars.partner_instructor') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.partner_instructor_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>23</td>
                            <td>
                                {{ trans('admin/pages/webinars.subscribe') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.subscribe_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>24</td>
                            <td>
                                {{ trans('admin/pages/webinars.private') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.private_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>25</td>
                            <td>
                                {{ trans('admin/pages/webinars.forum') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.forum_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>26</td>
                            <td>
                                {{ trans('admin/pages/webinars.enable_waitlist') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.enable_waitlist_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>27</td>
                            <td>
                                {{ trans('admin/pages/webinars.access_days') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.access_days_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>28</td>
                            <td>
                                {{ trans('admin/pages/webinars.points') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.points_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>29</td>
                            <td>
                                {{ trans('admin/pages/webinars.message_for_reviewer') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.message_for_reviewer_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>30</td>
                            <td>
                                {{ trans('admin/pages/webinars.seo_description') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.seo_description_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>31</td>
                            <td>
                                {{ trans('admin/pages/webinars.filters') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.filters_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>32</td>
                            <td>
                                {{ trans('admin/pages/webinars.tags') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.tags_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>33</td>
                            <td>
                                {{ trans('admin/pages/webinars.partners') }}
                                <span class="badge badge-info">{{ trans('admin/pages/webinars.optional') }}</span>
                            </td>
                            <td>{{ trans('admin/pages/webinars.partners_instruction') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Download Template Button -->
                <div class="mt-3">
                    <a href="{{ route('organization_instructor.webinars.download.template') }}" class="btn btn-primary btn-sm">
                        {{ trans('admin/pages/webinars.download_template') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
