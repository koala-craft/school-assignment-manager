# 個人成績表PDF出力

## 概要

API仕様書 10.4 で定義されている「個人成績表PDF出力」機能。

- **エンドポイント / 対象**: `GET /api/reports/student-grades/pdf?student_id=5&academic_year_id=1`
- **権限**: 管理者、該当学生本人
- **レスポンス / 出力**: PDFファイル

## 状態

- [ ] 未実装
- [ ] 実装中
- [x] 完了
- [ ] 保留

**優先度**: 中

## なぜ未実装だったか

1. **追加ライブラリの必要**
   - PDF生成には DomPDF や Snappy などのライブラリが必要
   - 既存の実装では CSV 出力で代替している

2. **実装の優先度**
   - CSV でも同様のデータを取得可能
   - まずは CSV で動作確認し、見た目重視の PDF は後回しにした

3. **ユーザー要望**
   - 「PDFは置いておいて」との指示により、他機能を優先

## 実装工数の目安

| 作業 | 見積もり |
|------|----------|
| ライブラリ導入（DomPDF 等） | 0.5h |
| Blade テンプレート作成 | 1-2h |
| Controller メソッド追加 | 0.5h |
| ルート追加・テスト | 0.5h |
| **合計** | **約 2.5-3.5 時間** |

## 実装するとできること

- 学生が自分の成績表を PDF 形式でダウンロード可能
- 印刷・提出用のフォーマットされた成績表を発行
- 管理者が学生の成績表を PDF で出力・印刷可能
- 学校や就活で必要な「成績証明書」的な書類の自動生成

## 実装状況（完了時のみ記入）

- [x] DomPDF ライブラリ導入（barryvdh/laravel-dompdf）
- [x] Blade テンプレート作成（resources/views/reports/student-grades-pdf.blade.php）
- [x] ReportController::studentGradesPdf メソッド
- [x] ルート追加・テストスクリプト（scripts/test-student-grades-pdf.ps1）

## 追加対応（必要な場合のみ）

DomPDF パッケージを追加したため、以下を実行してください。

```bash
docker compose exec app composer update
```

## 権限

| エンドポイント / 機能 | 権限 |
|----------------------|------|
| GET /reports/student-grades/pdf | 管理者、該当学生本人 |

## 依存関係（ある場合）

- 個人成績表CSV出力（studentGradesCsv）のロジックを流用
- 年度・科目・提出状況データが存在すること

## 技術メモ（任意）

- barryvdh/laravel-dompdf を使用（Blade テンプレートから PDF 生成）
- 日本語表示のため Hiragino Sans 等のフォントを指定
