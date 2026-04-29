<style>
    .badge-info { background-color: #3abaf4 !important; }
    .badge-success { background-color: #47c363 !important; }
</style>
<div class="row">
    <div class="col-12 col-md-12">
        <div class="card">
            <div class="mx-4 mt-4">
                <div>
                    <h4>{{ trans('product.excel_upload_instructions') }}</h4>
                </div>
                <div class="mt-1">
                    <p>{{ trans('product.follow_instructions') }}</p>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped font-14">
                    <thead>
                        <tr>
                            <th>{{ trans('product.column_number') }}</th>
                            <th>{{ trans('product.column_name') }}</th>
                            <th>{{ trans('product.instruction') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>{{ trans('product.type') }} 
                                <span class="badge badge-success" style="background-color: #47c363 !important;">{{ trans('product.required') }}</span></td>
                            <td>{{ trans('product.type_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>{{ trans('product.locale') }} <span class="badge badge-success">{{ trans('product.required') }}</span></td>
                            <td>{{ trans('product.locale_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>{{ trans('product.title') }} <span class="badge badge-success">{{ trans('product.required') }}</span></td>
                            <td>{{ trans('product.title_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>{{ trans('product.category_id') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.category_id_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>{{ trans('product.price') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.price_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>{{ trans('product.point') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.point_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>{{ trans('product.unlimited_inventory') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.unlimited_inventory_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>{{ trans('product.ordering') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.ordering_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>{{ trans('product.inventory') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.inventory_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>{{ trans('product.inventory_warning') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.inventory_warning_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>{{ trans('product.delivery_fee') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.delivery_fee_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>13</td>
                            <td>{{ trans('product.delivery_estimated_time') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.delivery_estimated_time_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>{{ trans('product.message_for_reviewer') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.message_for_reviewer_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>15</td>
                            <td>{{ trans('product.tax') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.tax_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>16</td>
                            <td>{{ trans('product.commission_type') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.commission_type_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>17</td>
                            <td>{{ trans('product.commission') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.commission_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>19</td>
                            <td>{{ trans('product.seo_description') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.seo_description_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>20</td>
                            <td>{{ trans('product.summary') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.summary_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>21</td>
                            <td>{{ trans('product.description') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.description_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>22</td>
                            <td>{{ trans('product.variants') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.variants_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>23</td>
                            <td>{{ trans('product.media') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.media_instruction') }}</td>
                        </tr>
                        <tr>
                            <td>24</td>
                            <td>{{ trans('product.filter_options') }} <span class="badge badge-info">{{ trans('product.optional') }}</span></td>
                            <td>{{ trans('product.filter_options_instruction') }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-3">
                    <a href="{{ route('instructor.products.download.template') }}" class="btn btn-primary">
                        {{ trans('product.download_template') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>