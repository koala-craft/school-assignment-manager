<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>個人成績表</title>
    <style>
        body { font-family: "Hiragino Sans", "Hiragino Kaku Gothic ProN", "Meiryo", sans-serif; font-size: 10pt; }
        h1 { font-size: 14pt; text-align: center; margin-bottom: 20px; }
        .header { margin-bottom: 20px; }
        .header table { width: 100%; }
        .header td { padding: 2px 0; }
        .label { font-weight: bold; width: 120px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.data th, table.data td { border: 1px solid #333; padding: 6px 8px; }
        table.data th { background-color: #f0f0f0; font-weight: bold; text-align: left; }
        table.data td { }
        .score { text-align: center; }
        .footer { margin-top: 30px; font-size: 8pt; color: #666; text-align: right; }
        .status-ja { font-size: 9pt; }
    </style>
</head>
<body>
    <h1>個人成績表</h1>

    <div class="header">
        <table>
            <tr><td class="label">氏名</td><td>{{ $student->name }}</td></tr>
            <tr><td class="label">学籍番号</td><td>{{ $student->student_number ?? '-' }}</td></tr>
            <tr><td class="label">対象年度</td><td>{{ $academicYear->name }}</td></tr>
            <tr><td class="label">発行日</td><td>{{ $issuedAt }}</td></tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>科目</th>
                <th>課題名</th>
                <th>提出期限</th>
                <th>状態</th>
                <th>提出日時</th>
                <th>スコア</th>
                <th>採点日時</th>
            </tr>
        </thead>
        <tbody>
            @forelse($submissions as $s)
            <tr>
                <td>{{ $s->assignment->subject->name ?? '-' }}</td>
                <td>{{ $s->assignment->title ?? '-' }}</td>
                <td>{{ $s->assignment->deadline?->format('Y/m/d H:i') ?? '-' }}</td>
                <td class="status-ja">{{ $statusLabels[$s->status] ?? $s->status }}</td>
                <td>{{ $s->submitted_at?->format('Y/m/d H:i') ?? '-' }}</td>
                <td class="score">{{ $s->score !== null ? $s->score : '-' }}</td>
                <td>{{ $s->graded_at?->format('Y/m/d H:i') ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">該当年度の成績データがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($submissions->isNotEmpty())
    <div class="header" style="margin-top: 15px;">
        <table>
            <tr><td class="label">平均点</td><td>{{ number_format($averageScore, 1) }}</td></tr>
        </table>
    </div>
    @endif

    <div class="footer">
        学校提出物管理システムにより発行　{{ $issuedAt }}
    </div>
</body>
</html>
