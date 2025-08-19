<!DOCTYPE html>
<html>
<head>
    <title>Remitted Records</title>
</head>
<body>

<h1>Remitted Records</h1>

@if(isset($remitted) && $remitted->count() > 0)
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Column 1</th>
                <th>Column 2</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($remitted as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->column_1 ?? '' }}</td>
                    <td>{{ $item->column_2 ?? '' }}</td>
                    <td>{{ $item->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No record found</p>
@endif

</body>
</html>
