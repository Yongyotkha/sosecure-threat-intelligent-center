<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Index of /feeds/</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background-color: #fff;
            color: #000;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 4px 8px;
        }
        th {
            border-bottom: 1px solid #aaa;
        }
        a {
            color: #00f;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .right {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Index of /feeds/</h1>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Last modified</th>
                <th class="right">Size</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><a href="{{ url('/') }}">Parent Directory</a></td>
                <td>-</td>
                <td class="right">-</td>
            </tr>
            @foreach($feedFiles as $file)
                <tr>
                    <td><a href="{{ $file['url'] }}">{{ $file['name'] }}</a></td>
                    <td>{{ $file['last_modified'] }}</td>
                    <td class="right">{{ number_format($file['size'] / 1024, 0) }}K</td>
                </tr>
            @endforeach
            <tr>
                    <td><a href="https://insights.sosecure.co.th/feeds/list/manifest.json">manifest.json</a></td>
                    <td></td>
                    <td class="right"></td>
                </tr>
        </tbody>
    </table>
</body>
</html>
