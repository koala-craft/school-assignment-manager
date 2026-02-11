/**
 * ヘルプ・FAQ 用の静的データ
 * 将来的に管理画面から編集可能にするオプションあり
 */

export type FaqCategoryId =
  | 'all'
  | 'login'
  | 'assignment'
  | 'grading'
  | 'subject'
  | 'profile'
  | 'other'

export interface FaqCategory {
  id: FaqCategoryId
  label: string
  icon: string
  description: string
}

export interface FaqItem {
  id: string
  categoryId: FaqCategoryId
  question: string
  answer: string
}

export const faqCategories: FaqCategory[] = [
  {
    id: 'all',
    label: '全て',
    icon: 'mdi-format-list-bulleted',
    description: 'すべてのカテゴリのFAQを表示します',
  },
  {
    id: 'login',
    label: 'ログイン・認証',
    icon: 'mdi-lock-outline',
    description: 'ログイン、パスワードリセット、初回ログイン時のパスワード変更',
  },
  {
    id: 'assignment',
    label: '課題・提出',
    icon: 'mdi-file-document-edit-outline',
    description: '学生向け：課題の確認、提出方法、提出履歴・成績',
  },
  {
    id: 'grading',
    label: '採点・提出物',
    icon: 'mdi-checkbox-marked-circle-outline',
    description: '教員向け：提出物の作成、提出状況、採点、テンプレート',
  },
  {
    id: 'subject',
    label: '科目・履修',
    icon: 'mdi-book-open-variant',
    description: '科目の登録・編集、履修管理、年度・学期',
  },
  {
    id: 'profile',
    label: 'プロフィール・通知',
    icon: 'mdi-account-cog-outline',
    description: 'プロフィール編集、通知の確認、パスワード変更',
  },
  {
    id: 'other',
    label: 'その他',
    icon: 'mdi-help-circle-outline',
    description: 'よくある質問、問い合わせ',
  },
]

export const faqItems: FaqItem[] = [
  // ログイン・認証
  {
    id: 'login-1',
    categoryId: 'login',
    question: 'ログインできない',
    answer:
      'メールアドレスとパスワードを確認してください。大文字・小文字、前後のスペースに注意してください。パスワードをお忘れの場合は、ログイン画面の「パスワードを忘れた場合」からパスワードリセットをご利用ください。リセット用のリンクが登録メールアドレスに送信されます。',
  },
  {
    id: 'login-2',
    categoryId: 'login',
    question: '初回ログインでパスワード変更画面に飛ばされる',
    answer:
      'セキュリティのため、初回ログイン時は必ずパスワードの変更が必要です。現在の仮パスワードと、新しいパスワード（8文字以上）を入力して変更を完了してください。変更後はダッシュボードに移動します。',
  },
  {
    id: 'login-3',
    categoryId: 'login',
    question: 'パスワードリセットのメールが届かない',
    answer:
      '迷惑メールフォルダをご確認ください。届かない場合はメールアドレスの入力ミスや、システムで登録されているメールアドレスと異なる可能性があります。管理者に登録メールアドレスの確認を依頼してください。',
  },
  // 課題・提出（学生）
  {
    id: 'assignment-1',
    categoryId: 'assignment',
    question: '課題の提出方法',
    answer:
      '学生ダッシュボードまたは「課題一覧」から、提出したい課題を選び詳細画面を開いてください。「提出」ボタンをクリックし、必要なファイルをアップロード（ドラッグ＆ドロップも可）し、任意でコメントを入力してから提出を確定してください。締切時刻を過ぎると提出できません。',
  },
  {
    id: 'assignment-2',
    categoryId: 'assignment',
    question: '提出できるファイル形式・サイズ',
    answer:
      '課題ごとに教員が指定した形式・サイズが異なります。課題詳細画面の「提出形式」「許可ファイル形式」「最大ファイルサイズ」を確認してください。指定外の形式やサイズ超過のファイルはアップロードできません。',
  },
  {
    id: 'assignment-3',
    categoryId: 'assignment',
    question: '締切を過ぎてしまった',
    answer:
      '締切を過ぎると通常は提出できません。やむを得ない事情がある場合は、担当教員に連絡し、再提出の指示や特別な対応があるか確認してください。教員から「再提出」を指示された場合は、指示に従って再提出できます。',
  },
  {
    id: 'assignment-4',
    categoryId: 'assignment',
    question: '採点結果の確認方法',
    answer:
      '「提出履歴・成績」画面で、採点済みの課題の点数・評価と教員コメントを確認できます。科目別・年度別に表示されます。個人成績表のPDF出力もこの画面から行えます。',
  },
  // 採点・提出物（教員）
  {
    id: 'grading-1',
    categoryId: 'grading',
    question: '提出物の作り方',
    answer:
      '科目管理から該当科目を開き、「提出物管理」で「新規作成」をクリックします。タイトル・説明・提出期限・提出形式（ファイル/テキスト/両方）・許可ファイル形式・最大サイズなどを設定し、保存してください。テンプレートから作成することもできます。',
  },
  {
    id: 'grading-2',
    categoryId: 'grading',
    question: '提出状況の確認方法',
    answer:
      '科目の提出物一覧で各提出物の「提出状況」を開くと、学生ごとの提出状況がマトリクスで表示されます。未提出・提出済み・採点済みを一覧で確認でき、セルをクリックすると詳細・採点画面に進みます。',
  },
  {
    id: 'grading-3',
    categoryId: 'grading',
    question: '採点の仕方',
    answer:
      '「採点」メニューから未採点一覧を表示し、学生を選ぶと右側に提出内容と採点欄が表示されます。点数または評価と、任意で教員コメントを入力して保存してください。提出状況マトリクスから該当セルをクリックして採点することもできます。',
  },
  {
    id: 'grading-4',
    categoryId: 'grading',
    question: 'テンプレートの使い方',
    answer:
      '「テンプレート」一覧で既存のテンプレートを選び「使用」をクリックすると、その内容で新しい提出物の作成画面が開きます。科目と締切などを設定するだけで、説明や提出形式を流用できます。よく使う形式はテンプレート化しておくと便利です。',
  },
  // 科目・履修
  {
    id: 'subject-1',
    categoryId: 'subject',
    question: '科目の登録・編集',
    answer:
      '教員・管理者は「科目管理」から科目を一覧表示し、新規作成または編集できます。科目コード・科目名・年度・学期・担当教員を設定します。科目ごとに「履修管理」で履修学生を登録し、「提出物管理」で課題を作成します。',
  },
  {
    id: 'subject-2',
    categoryId: 'subject',
    question: '履修学生の追加・解除',
    answer:
      '科目の「履修管理」画面で、左側に現在の履修学生一覧、右側に学生検索・追加エリアがあります。検索して学生を選び「追加」で履修登録できます。解除する場合は履修一覧の「解除」をクリックしてください。CSVの一括インポートにも対応しています。',
  },
  {
    id: 'subject-3',
    categoryId: 'subject',
    question: '年度・学期の切り替え',
    answer:
      '年度・学期は管理者が「年度管理」「学期管理」で登録・編集します。科目作成時に年度・学期を選択するため、事前に必要な年度・学期を登録しておいてください。有効な年度を切り替えると、科目一覧などの表示対象が変わります。',
  },
  // プロフィール・通知
  {
    id: 'profile-1',
    categoryId: 'profile',
    question: '名前やメールアドレスを変更したい',
    answer:
      '画面上部のユーザーメニューから「プロフィール」を開き、氏名・メールアドレスを編集して保存してください。メールアドレスはログインやパスワードリセットに使用するため、正確に入力してください。',
  },
  {
    id: 'profile-2',
    categoryId: 'profile',
    question: 'パスワードを変更したい',
    answer:
      'ユーザーメニューから「パスワード変更」を選び、現在のパスワードと新しいパスワード（8文字以上）を入力して変更してください。定期的な変更を推奨しています。',
  },
  {
    id: 'profile-3',
    categoryId: 'profile',
    question: '通知の確認方法',
    answer:
      'ヘッダーのベルアイコンから「通知一覧」を開けます。未読・全ての切り替えや、各通知をクリックして詳細を確認できます。提出期限のリマインダーや採点完了など、設定に応じて通知が届きます。',
  },
  // その他
  {
    id: 'other-1',
    categoryId: 'other',
    question: '画面が表示されない・エラーになる',
    answer:
      'ブラウザを最新版に更新し、キャッシュを削除してから再度アクセスしてください。それでも改善しない場合は、別のブラウザやデバイスで試すか、管理者にお問い合わせください。',
  },
  {
    id: 'other-2',
    categoryId: 'other',
    question: '問い合わせ・サポート',
    answer:
      'このヘルプで解決しない場合は、学校の運用担当者またはシステム管理者に問い合わせてください。操作手順の説明や、不具合の報告時に画面のキャプチャやエラーメッセージがあるとスムーズです。',
  },
]
