<html>
    <table>
        <thead>
            <tr>
                <th style="font-size: 18px">{{translate('subscriber_List')}}</th>
            </tr>
            <tr>
                <th>{{ translate('Subscriber_analytics').' '.'-' }}</th>
                <th>
                    {{translate('total_subscriber').' '.'-'.' '.count($data['subscription'])}}
                </th>
            </tr>
            <tr>
                <th>{{ translate('Filter_Criteria').' '.'-' }}</th>
                <th>
                    {{ translate('sort_by') . ' - ' . (!empty($data['sortBy']) ?  ($data['sortBy']  === 'asc' ? translate('Sort_By_Oldest') : translate('Sort_By_Newest')) : 'N/A') }}
                    <br>
                    {{ translate('choose_first'). ' - ' . (!empty($data['chooseFirst']) ? $data['chooseFirst'] : 'N/A')}}
                    <br>
                    {{ translate('search_Bar_Content'). ' - ' . (!empty($data['search']) ? $data['search'] : 'N/A')}}
                    <br>
                    {{translate('start_date').' - '. (!empty($data['startDate']) ?  $data['startDate']->format('d F Y') : 'N/A') }}
                    <br>
                    {{translate('end_date').' - '. (!empty($data['endDate']) ?  $data['endDate']->format('d F Y') : 'N/A') }}
                </th>
            </tr>
            <tr>
                <td> {{translate('SL')}}	</td>
                <td> {{translate('Email_ID')}}</td>
                <td> {{translate('subscription_Date')}}</td>
            </tr>
            @foreach ($data['subscription'] as $key=>$item)
                <tr>
                    <td> {{++$key}}	</td>
                    <td>{{$item->email}}</td>
                    <td> {{date('d M, Y',strtotime($item->created_at))}}</td>
                </tr>
            @endforeach
        </thead>
    </table>
</html>
