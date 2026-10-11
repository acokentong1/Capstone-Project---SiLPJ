<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>
        @yield ('title', 'Pemeriksaan LPJ')
        — SiLPJ
    </title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: #f4f7fb;
            color: #172033;
            font:
                15px/1.55 system-ui,
                sans-serif;
        }
        main {
            max-width: 1180px;
            margin: 32px auto;
            padding: 0 20px 48px;
        }
        h1 {
            font-size: 28px;
            margin: 0;
        }
        h2 {
            font-size: 19px;
            margin: 0 0 16px;
        }
        a {
            color: #1d4ed8;
        }
        small,
        .muted {
            color: #64748b;
        }
        .hero {
            margin-bottom: 24px;
        }
        .card {
            background: white;
            border: 1px solid #dfe5ef;
            border-radius: 14px;
            padding: 22px;
            margin: 18px 0;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }
        .metric {
            padding: 18px;
            background: white;
            border: 1px solid #dfe5ef;
            border-radius: 12px;
        }
        .metric strong {
            display: block;
            font-size: 28px;
        }
        .split {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
        }
        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            background: #e8eef8;
            font-weight: 650;
            font-size: 12px;
        }
        .diajukan {
            background: #fff1c7;
            color: #854d0e;
        }
        .disetujui {
            background: #dcfce7;
            color: #166534;
        }
        .revisi {
            background: #fee2e2;
            color: #991b1b;
        }
        .btn,
        button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 10px 15px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font: inherit;
            font-weight: 600;
        }
        .secondary {
            background: #e8eef8;
            color: #1e40af;
        }
        .danger {
            background: #b91c1c;
        }
        .ok {
            padding: 12px;
            background: #dcfce7;
            border-radius: 8px;
        }
        .error {
            padding: 12px;
            background: #fee2e2;
            border-radius: 8px;
        }
        input,
        select,
        textarea {
            font: inherit;
            border: 1px solid #cbd5e1;
            padding: 10px;
            border-radius: 7px;
            width: 100%;
            background: white;
        }
        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 600;
        }
        .filters {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
        }
        .filters > div {
            flex: 1;
            min-width: 170px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        th {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
        }
        .scroll {
            overflow-x: auto;
        }
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .event {
            border-left: 3px solid #cbd5e1;
            padding: 0 0 18px 16px;
            margin-left: 5px;
        }
        .event p {
            margin: 5px 0;
        }
        pre {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            font-size: 12px;
            background: #f8fafc;
            padding: 12px;
        }
        dl {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 10px;
        }
        dt {
            color: #64748b;
        }
        dd {
            margin: 0;
            overflow-wrap: anywhere;
        }
        .inline-form {
            margin: 14px 0;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        summary {
            cursor: pointer;
            color: #1d4ed8;
        }
        .pagination {
            margin-top: 20px;
        }
        .pagination svg {
            width: 20px;
        }
        .file {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        @media (max-width: 760px) {
            .split {
                grid-template-columns: 1fr;
            }
            .grid {
                grid-template-columns: repeat(2, 1fr);
            }
            main {
                padding: 0 12px 24px;
            }
            .card {
                padding: 16px;
            }
            h1 {
                font-size: 24px;
            }
            dl {
                grid-template-columns: 1fr;
                gap: 3px;
            }
            dd {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    @include ('partials.app-navigation')
    <main>
        @if (session('success'))
            <p class="ok" role="status">{{
                session(
                    'success',
                )
            }}</p>
        @endif
        @if (session('error'))
            <p class="error" role="alert">{{
                session(
                    'error',
                )
            }}</p>
        @endif
        @if ($errors->any())
            <div class="error" role="alert">
                <strong>Periksa kembali:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield ('content')
    </main>
</body>
</html>
