<data-table
        :headers="{{json_encode($headers)}}"
        action="{{$action}}"

>
</data-table>
<pre>{{json_encode($headers,JSON_PRETTY_PRINT)}}</pre>