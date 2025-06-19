<html>
    <table>
        <thead>
            <tr>
                <th style="font-size: 18px">{{(translate('restock_product_list'))}}</th>
            </tr>
            <tr>
                <th>{{ translate('Restock_Analytics').' '.'-' }}</th>
                <th></th>
                <th>
                    {{translate('total_Restock_Request').' '.'-'.' '.count($data['products'])}}
                </th>
            </tr>
            <tr>
                <th>
                    {{ translate('filter_Criteria').' - ' }}
                </th>
                <th></th>
                <th>
                    {{translate('category').' - '. ($data['category'] != 'all' ? $data['category']['name'] : $data['category'])  }}
                    <br>
                    {{translate('sub_Category').' - '. ($data['subCategory'] != 'all' ? $data['subCategory']['name'] : $data['subCategory'])  }}
                    <br>
                    {{translate('brand').' - '. ($data['brand'] != 'all' ? $data['brand']['name'] : $data['brand'])  }}
                    <br>
                    {{translate('search_Bar_Content').' - '. (!empty($data['searchValue']) ?  ucwords($data['searchValue']) : 'N/A') }}
                    <br>
                    {{translate('start_date').' - '. (!empty($data['startDate']) ?  $data['startDate']->format('d F Y') : 'N/A') }}
                    <br>
                    {{translate('end_date').' - '. (!empty($data['endDate']) ?  $data['endDate']->format('d F Y') : 'N/A') }}
                </th>
            </tr>
            <tr>
                <td> {{translate('SL')}}</td>
                <td> {{translate('product_Image')}}	</td>
                <td> {{translate('image_URL')}}	</td>
                <td> {{translate('product_Name')}}	</td>
                <td> {{translate('selling_price')}}</td>
                <td> {{translate('variant')}}</td>
                <td> {{translate('last_request_date')}}</td>
                <td> {{translate('number_of_Request')}}</td>
            </tr>
            @foreach ($data['products'] as $key=>$item)
                <tr>
                    <td> {{++$key}}	</td>
                    <td style="height: 200px"></td>
                    <td>{{dynamicStorage(path: 'storage/app/public/product/thumbnail/'.$item?->product->thumbnail)}}</td>
                    <td> {{$item?->product?->name}}</td>
                    <td>{{$item?->product?->unit_price}}</td>
                    <td>{{ $item['variant'] ?? translate('no_variation') }}</td>
                    <td>{{ $item->updated_at->format('d F Y, h:i A')}}</td>
                    <td>{{ $item?->restockProductCustomers?->count() ?? 0}}</td>
                </tr>
            @endforeach
        </thead>
    </table>
</html>
