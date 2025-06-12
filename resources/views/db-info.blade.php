<!DOCTYPE html>
<html>
<head>
    <title>Database Information</title>
    <style>
        table { border-collapse: collapse; margin-bottom: 20px; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .table-header { font-size: 22px; letter-spacing: -1px; }
        .table-comment { font-size: 15px; letter-spacing: 0px; }
    </style>
</head>
<body>
@foreach($tablesData as $tableData)
    <table>
        <thead>
        <tr>
            <th colspan="7" align="center" class="table-header">
                {{ $tableData['table']->TABLE_NAME }}
                <span class="table-comment">({{ $tableData['table']->TABLE_COMMENT }})</span>
            </th>
        </tr>
        <tr>
            <th>Column name</th>
            <th>Key</th>
            <th>Type</th>
            <th>Default</th>
            <th>Nullable</th>
            <th>Extra</th>
            <th>Comment</th>
        </tr>
        </thead>
        <tbody>
        @foreach($tableData['columns'] as $column)
            <tr>
                <td>{{ $column->COLUMN_NAME }}</td>
                <td>{{ $column->COLUMN_KEY }}</td>
                <td>{{ $column->COLUMN_TYPE }}</td>
                <td>{{ $column->COLUMN_DEFAULT }}</td>
                <td>{{ $column->IS_NULLABLE }}</td>
                <td>{{ $column->EXTRA }}</td>
                <td>{{ $column->COLUMN_COMMENT }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endforeach
</body>
</html>
