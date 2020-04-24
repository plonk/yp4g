YP4G PeerCast Yellow Pages

◆使い方
１．yp4g.cfg.php、reload.php、uptest_srv.confを適切に設定します。
    (php の場合は、一番上に設定すべき変数があります)
    変数名が同じ場合は同じ値を設定してください。

２．統計・チャット用データベースファイルを置くディレクトリの
    パーミッションを適切に設定します。
    (Webサーバ、reload.php、uptest_srvから読み書きできるようにする)

３．dbsetup.php を実行しデータベースを作成します。

４．PeerCast を Root モードで起動します

５．reload.php をタスクスケジューラなどで一定間隔で実行させます
    (PeerCastの設定の Root Mode -> Host Update と同じ間隔。普通は60秒で)

６．帯域測定をする場合は uptest_srv も実行します

◆その他
・チャンネルを漏れ難くする設定
  ・Max. Controls In を想定する最大チャンネル数より大きな値にする
  ・ini の maxServIn の値を Max. Controls In より余裕を持った大きな値にする

◆ライセンス
・GPLに準じます。
・Powered by YP4G の表記を改変及び削除しないで下さい。

◆e5bW6vDOJ.
