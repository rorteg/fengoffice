<?php

  /**
  * Error messages
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */

  // Return langs
  return array(

    // General
    'invalid email address' => '電子メールアドレスが正しい書式ではありません。',
	'error invalid recipients' => 'フィールド"{0}"に不正な電子メールアドレスを発見: {1}',

    // Company validation errors
    'company name required' => '会社・組織の名前が必要です。',
    'company homepage invalid' => 'ホームページが正しいURLではありません。',

    // User validation errors
    'username value required' => 'ユーザー名が必要です。',
    'username must be unique' => '申し訳ありませんが、選択したユーザー名は既に使用されています。',
    'email value is required' => '電子メールアドレスが必要です。',
    'email address must be unique' => '申し訳ありませんが、選択した電子メールアドレスは既に使用されています。',
    'company value required' => 'ユーザーは会社・組織に所属していなければなりません。',
    'password value required' => 'パスワードが必要です。',
    'passwords dont match' => 'パスワードが一致しません。',
    'old password required' => '古いパスワードが必要です。',
    'invalid old password' => '古いパスワードが誤っています。',
    'users must belong to a company' => 'ユーザーを作成するには、連絡先は会社に所属していなければなりません。',
    'contact linked to user' => '連絡先がユーザー{0}と関連付けられました。',

    // Password validation errors
    'password invalid min length' => 'パスワードの長さは{0}文字以上でなければなりません。',
    'password invalid numbers' => 'パスワードは{0}文字以上の数字を含まなければなりません。',
    'password invalid uppercase' => 'パスワードは{0}文字以上の大文字を含まなければなりません。',
    'password invalid metacharacters' => 'パスワードは{0}文字以上のメタ文字(記号)を含まなければなりません。',
    'password exists history' => 'パスワードは過去に使用された10個と同じです。',
    'password invalid difference' => 'パスワードは過去に使用された10個と、3文字以上異なっていなければなりません。',
    'password expired' => 'パスワードの有効期限が切れています。',
    'password invalid' => 'パスワードは既に無効です。',

    // Avatar
    'invalid upload type' => 'ファイルのタイプが不正です。使用できるファイルタイプは{0}です。',
    'invalid upload dimensions' => '画像のピクセル値が大きすぎます。最大の大きさは{0}x{1}ピクセルです。',
    'invalid upload size' => '画像のファイルが大きすぎます。最大の大きさ{0}です。',
    'invalid upload failed to move' => 'アップロードしたファイルを移動できませんでした。',

    // Registration form
    'terms of services not accepted' => 'アカウントを作成するには、サービスの規約を読んで承認しなければなりません。',

    // Init company website
    'failed to load company website' => 'ウェブサイトを読み込めませんでした。オーナー会社が見つかりません。',
    'failed to load project' => '活動中のワークスペースを読み込めませんでした。',

    // Login form
    'username value missing' => 'ユーザー名を入力してください。',
    'password value missing' => 'パスワードを入力してください。',
    'invalid login data' => 'ログインできませんでした。 ログイン情報を確認して再度実行してください。',

    // Add project form
    'project name required' => 'ワークスペース名が必要です。',
    'project name unique' => '同じワークスペース名は使用できません。',

    // Add message form
    'message title required' => '表題が必要です。',
    'message title unique' => 'ワークスペースの中で同じ表題は使用できません。',
    'message text required' => '本文が必要です。',

    // Add comment form
    'comment text required' => 'コメントの本文が必要です。',

    // Add milestone form
    'milestone name required' => 'マイルストーン名が必要です。',
    'milestone due date required' => 'マイルストーンの期日が必要です。',

    // Add task list
    'task list name required' => 'タスク名が必要です。',
    'task list name unique' => 'ワークスペースの中で同じタスク名は使用できません。',
    'task title required' => 'タスクの表題が必要です。',

    // Add task
    'task text required' => 'タスクの本文が必要です。',
    'repeat x times must be a valid number between 1 and 1000' => '繰り返しの回数は1から1000まで間の有効な値でなければなりません。',
    'repeat period must be a valid number between 1 and 1000' => '繰り返しの期間は1から1000までの間の有効な値でなければなりません。',
    'to repeat by start date you must specify task start date' => '開始日で繰り返すには、タスクの開始日を指示しなければなりません。',
    'to repeat by due date you must specify task due date' => '期日で繰り返すには、タスクの期日を指示しなければなりません。',
    'task cannot be instantiated more times' => 'これ以上の回数のタスクを生成できません。このため、これが最後の繰り返しとなります。',

    // Add event
    'event subject required' => 'イベントの件名が必要です。',
    'event description maxlength' => '説明は3000文字未満にしてください。',
    'event subject maxlength' => '件名は100文字未満にしてください。',

    // Add project form
    'form name required' => 'フォームの名前が必要です。',
    'form name unique' => '同じフォームの名前は使用できません。',
    'form success message required' => 'サクセスノートが必要です。',
    'form action required' => 'フォームにactionが必要です。',
    'project form select message' => 'ノートを選択してください。',
    'project form select task lists' => 'タスクを選択してください。',

    // Submit project form
    'form content required' => 'テキストフィールドの内容を入力してください。',

    // Validate project folder
    'folder name required' => 'フォルダー名が必要です。',
    'folder name unique' => 'ワークスペース内で同じフォルダー名は使用できません。',

    // Validate add / edit file form
    'folder id required' => 'フォルダーを選択してください。',
    'filename required' => 'ファイル名が必要です。',
    'weblink required' => 'ウェブリンクにはURLが必要です。',

    // File revisions (internal)
    'file revision file_id required' => 'リビジョンはファイルに結合されていなければなりません。',
    'file revision filename required' => 'ファイル名が必要です。',
    'file revision type_string required' => 'ファイルの種類が不明です。',
    'file revision comment required' => 'リビジョンのコメントが必要です。',

    // Test mail settings
    'test mail recipient required' => '宛先のアドレスが必要です。',
    'test mail recipient invalid format' => '宛先のアドレスの書式に誤りがあります。',
    'test mail message required' => 'メールのメッセージが必要です。',

    // Mass mailer
    'massmailer subject required' => 'メッセージの件名が必要です。',
    'massmailer message required' => 'メッセージの本文が必要です',
    'massmailer select recepients' => 'メールを受信するユーザーを選択してください。',

    // Email module
    'mail account name required' => 'アカウントの名前が必要です。',
    'mail account id required' => 'アカウントのIDが必要です。',
    'mail account server required' => 'サーバーの指定が必要です。',
    'mail account password required' => 'パスワードが必須です。',
    'send mail error' => 'メールの送信中にエラーとなりました。おそらくSMTPの設定が誤っています。',
    'email address already exists' => '電子メールアドレスは既に使用されています。',

    'session expired error' => 'ユーザーが活動していなかったため、セッションが期限切れとなりました。もう一度ログインしてください。',
    'unimplemented type' => '実装されていない種類',
    'unimplemented action' => '実装されていない動作',

    'workspace own parent error' => 'ワークスペースは自分自身の親のワークスペースになれません。',
    'task own parent error' => 'タスクは自分自身の親のタスクになれません。',
    'task child of child error' => 'タスクは自分の子供の子孫のタスクになれません。',

    'chart title required' => 'チャートの表題が必要です。',
    'chart title unique' => '同じチャートの同じ表題は使用できません。',
    'must choose at least one workspace error' => '少なくとも1つのワークスペースをオブジェクトを置くために選択しなければなりません。',


    'user has contact' => 'このユーザーは連絡先は既に関連付けられています。',

    'maximum number of users reached error' => 'ユーザーの最大数に達しました。',
    'maximum number of users exceeded error' => 'ユーザーの最大数を超えています。この問題を解決するまで、アプリケーションは今後は動作しません。',
    'maximum disk space reached' => 'ディスクの割り当て量が満杯です。新しく何か追加する前に不要なものを削除するか、ユーザーへの割当量を増やすようにサポート先に連絡してください。',
    'name must be unique' => '申し訳ありせんが、選択した名前はすでに使用されています。',
    'not implemented' => '実装されていません。',
    'return code' => 'リターンコード: {0}',
    'task filter criteria not recognised' => 'タスクのフィルターの判定基準\'{0}\'を認識できません。',
    'mail account dnx' => 'メールアカウントが存在しません。',
    'error document checked out by another user' => 'ドキュメントは他のユーザーがチェックアウト中です。',
    //Custom properties
    'custom property value required' => '{0}が必要です。',
    'value must be numeric' => '{0}の値は数値でなければなりません。',
    'values cannot be empty' => '{0}の値は空にできません。',

    //Reports
    'report name required' => 'レポートの名前が必要です。',
    'report object type required' => 'レポートにオブジェクトの種類が必要です。',

    'error assign task user dnx' => '存在しないユーザーを担当者にしようとしています。',
    'error assign task permissions user' => 'ユーザーをタスクの担当者に設定する権限がありません。',
    'error assign task company dnx' => '存在しない会社をタスクの担当にしようとしています。',
    'error assign task permissions company' => '会社をタスクの担当に設定する権限がありません。',
    'account already being checked' => 'アカウントは既に確認済みです。',
    'no files to compress' => '圧縮するファイルがありません。',

    //Subscribers

    'cant modify subscribers' => '登録者を変更できません。',
    'this object must belong to a ws to modify its subscribers' => 'オブジェクトの登録者を変更するには、オブジェクトはワークスペースに属していなければなりません。',

    'mailAccount dnx' => '電子メールアカウントが存在しません。',
    'error add contact from user' => 'ユーザーから連絡先を追加できませんでした。',
    'zip not supported' => 'ZIPがサーバーでサポートされていません。',
    'no tag specified' => 'タグが指定されていません。',
  
    'no mailAccount error' => '電子メールのアカウントを追加していないため、利用できません。',
	'content too long not loaded' => '電子メールの内容が大きすぎ、読み込んでいませんが、電子メールを送信します。'
  ); // array

?>
