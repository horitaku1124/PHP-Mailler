<?php

/**
 * メール送信インターフェース
 */
interface Mailler{
	/**
	 * メールを送信を受け付けるメソッド
	 *
	 * @param $info = array(
	 *   "to" => 送信先メールアドレス
	 *   "from" => 送信元アドレス
	 *   "subject" => 件名
	 *   "body" = 本文
	 * )
	 */
	public function send($info = array());
}