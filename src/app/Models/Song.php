<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    /**
     * 複数代入（Mass Assignment）を許可する属性
     *
     * @var array
     */
    protected $fillable = [
        'title',   // 曲のタイトル
        'content', // コード譜のテキストデータ
        'songs',
        'created_at',
        'updated_at',
    ];

    /**
     * 補足：もしデータをJSON形式で保存したい場合や、
     * 取り出す際に自動で配列に変換したい場合は casts を使用しますが、
     * 今回はシンプルなテキスト（textareaの文字列）なのでこれだけで十分です。
     */
}