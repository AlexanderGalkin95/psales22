{{-- Body --}}
<div style="max-width: 100%!important;">
    <table style="width:100%;font-size: x-small!important;">
        <thead>
        <tr>
            <th width="20%">Домен</th>
            <th width="20%">Статус</th>
            <th width="60%">Комментарий</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reportableData as $item)
        <tr>
            <td>{{ $item->domain }}</td>
            <td style="text-align: center; ">
                <span style="color: {{ $item->error ?  'darkred' : 'green'}};">
                    {{ !$item->error ? 'OnLine' : 'Error' }}
                </span>
            </td>
            <td>
                <div>
                    <code style='overflow-wrap: anywhere;'>{{ $item->error ?: 'Токены обновлены успешно!' }}</code>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

