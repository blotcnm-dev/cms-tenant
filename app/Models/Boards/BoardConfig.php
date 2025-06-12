<?php

namespace App\Models\Boards;

use Exception;
use App\Traits\ModelTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoardConfig extends Model
{
    use HasFactory, ModelTrait;

    public $table = "bl_board_configs";
    public string $tableKoNm = "게시판 설정";
    public $primaryKey = 'board_config_id';
    public $timestamps = false;
    protected $guarded = [];

    public function getBoardConfigs(array $params, bool $paginate = false): array
    {
        try {
            $query = DB::table($this->table);

            if (isset($params['select'])) {
                $query->select($params['select']);
            } else {
                $query->select('board_config_id', 'board_name', 'board_type', 'board_id', 'is_active', 'created_at', 'updated_at', 'is_category');
            }

            // 게시판명
            if (isset($params['board_name']) && !empty($params['board_name'])) {
                $query->where('board_name', 'like', '%'.$params['board_name'].'%');
            }

            // 사용 유무
            if (isset($params['is_active']) && $params['is_active'] != "") {
                $query->where('is_active', $params['is_active']);
            };

            // 게시판 구분
            if (isset($params['board_type'])) {
                $query->where('board_type', $params['board_type']);
            };

            // 게사판 구분
            if (isset($params['is_default'])) {
                $query->where('is_default', $params['is_default']);
            };

            $query->where('is_deleted', 0);

            // 정렬
            if (isset($params['order']) && isset($params['by'])) {
                if (!empty($params['order']) && !empty($params['by'])) {
                    $query = $query->orderBy($params['order'], $params['by']);
                }
            }

            // 갯수제한
            if (isset($params['limit']) && !empty($params['limit'])) {
                $query = $query->limit($params['limit']);
            }

            // 리턴 종류
            return $this->setReturnFormat($params, $paginate, $query);
        } catch (QueryException | Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * board db schema create
     *
     * @param array $params
     * @return array
     */
    public function createBoardTable(array $params) : array
    {
        try
        {
            $postTableName = 'bl_board_' . $params['board_id'] . "_posts";
            $replyTableName = 'bl_board_' . $params['board_id'] . "_replies";
            $fileTableName = 'bl_board_' . $params['board_id'] . "_files";
            $likeTableName = 'bl_board_' . $params['board_id'] . "_likes";

            if (Schema::hasTable($postTableName)) {
                throw new Exception("등록된 게시판이 존재합니다.");
            }

            // 게시판 테이블 생성
            Schema::create($postTableName, function (Blueprint $table) : void {
                $table->increments('post_id')->unsigned()->comment('게시글 아이디');
                $table->integer('post_num')->default(0)->comment('게시글의 답글 정렬을 위한 게시글 번호');
                $table->string('post_reply', 10)->default('')->comment('게시글의 답글 정보');
                $table->tinyInteger('is_display')->unsigned()->default(1)->comment('게시글 노출 여부');
                $table->integer('board_category_id')->unsigned()->default(0)->comment('게시판 분류 아이디');
                $table->string('banner')->default('')->comment('대표 이미지');
                $table->tinyInteger('is_main_banner')->default(0)->comment('메인 배너 ( 0 : 미사용, 1 : 사용 )');
                $table->string('category', 30)->default('')->comment('게시글 카테고리');
                $table->string('subject')->default('')->comment('게시글 제목');
                $table->text('content')->comment('게시글 내용');
                $table->integer('hits')->unsigned()->default(0)->comment('조회수');
                $table->tinyInteger('is_approval')->default(0)->comment('승인 여부');
                $table->string('reject_reason')->default('')->comment('승인 반려 사유');
                $table->tinyInteger('is_best')->default(0)->comment('베스트 여부');
                $table->dateTime('best_updated_at')->nullable()->comment('베스트 수정일');
                $table->tinyInteger('is_secret')->default(0)->comment('비밀글 여부');
                $table->string('secret_password', 255)->default('')->comment('비밀글 비밀번호');
                $table->integer('admin_id')->unsigned()->default(0)->comment('관리자 시퀀스');
                $table->integer('member_id')->unsigned()->default(0)->comment('작성자 시퀀스');
                $table->string('reply_status', 30)->default('READY')->comment('문의 답변 상태 (READY : 답변대기, ONGOING : 답변중, COMPLETE : 답변완료 )');
                $table->integer('inquiry_type_id')->unsigned()->default(0)->comment('문의 유형 아이디');
                $table->tinyInteger('likes')->default(0)->comment('좋아요수');
                $table->string('writer_name', 50)->default('')->comment('작성자 이름');
                $table->integer('writer_ip')->unsigned()->default(0)->comment('작성자 아이피');
                $table->integer('member_point_id')->unsigned()->default(0)->comment('포인트 시퀀스');
                $table->integer('extra_member_point_id')->unsigned()->default(0)->comment('추가 포인트 시퀀스');
                $table->tinyInteger('is_aligo')->default(0)->comment('알림톡 알림받기 ( 0 : 미사용 , 1 : 사용 )');
                $table->string('phone', 15)->default('')->comment('전화번호');
                $table->tinyInteger('is_email')->default(0)->comment('이메일 알림받기 ( 0 : 미사용 , 1 : 사용 )');
                $table->string('email', 100)->default('')->comment('이메일주소');
                $table->dateTime('created_at')->comment('등록일');
                $table->dateTime('updated_at')->comment('수정일');

                // 인덱스
                $table->index(['created_at'], 'index_created_at');
                $table->index(['subject'], 'index_subject');
                $table->index(['is_best'], 'index_is_best');
                $table->index(['likes'], 'index_likes');

            });
            DB::statement("ALTER TABLE " . $postTableName ." comment '" . $params['board_name'] . " 게시판'");

            // 댓글 테이블 생성
            Schema::create($replyTableName, function (Blueprint $table) : void {
                $table->increments('id')->unsigned()->comment('댓글 아이디');
                $table->integer('post_id')->unsigned()->default(0)->comment('게시글 아이디');
                $table->integer('cmt_num')->default(0)->comment('코멘트의 답글 정렬을 위한 게시글 번호');
                $table->string('cmt_reply', 10)->default('')->comment('코멘트의 답글 정보');
                $table->tinyInteger('is_secret')->default(0)->comment('비밀글 여부');
                $table->string('secret_password', 255)->default('')->comment('비밀글 비밀번호');
                $table->integer('admin_id')->unsigned()->default(0)->comment('관리자 아이디');
                $table->integer('member_id')->unsigned()->default(0)->comment('사용지 아이디');
                $table->integer('likes')->unsigned()->default(0)->comment('좋아요수');
                $table->text('content')->comment('댓글 내용');
                $table->string('writer_name', 50)->default('')->comment('작성자 이름');
                $table->integer('writer_ip')->unsigned()->default(0)->comment('작성자 아이피');
                $table->dateTime('created_at')->comment('등록일');
                $table->dateTime('updated_at')->comment('수정일');

                // 인덱스
                $table->index(['post_id', 'created_at'], 'index_post_id_created_at');
            });
            DB::statement("ALTER TABLE " . $replyTableName ." comment '" . $params['board_name'] . " 댓글'");

            // 파일 테이블 생성
            Schema::create($fileTableName, function (Blueprint $table) : void {
                $table->increments('id')->unsigned()->comment('파일 아이디');
                $table->integer('post_id')->unsigned()->default(0)->comment('게시글 아이디');
                $table->string('post_type', 30)->default('POSTS')->comment('POSTS, REPLIES, ETC');
                $table->string('ftype', 100)->default('')->comment('파일타입');
                $table->string('fsize', 100)->default('')->comment('파일사이즈');
                $table->string('path')->default('')->comment('원본 파일 경로');
                $table->string('fname')->default('')->comment('파일명');
                $table->string('thumbnail')->default('')->comment('썸네일');
                $table->dateTime('created_at')->comment('등록일');

                // 인덱스
                $table->index(['post_id', 'ftype', 'created_at'], 'index_post_id_ftype');
            });
            DB::statement("ALTER TABLE " . $fileTableName ." comment '" . $params['board_name'] . " 첨부파일'");

            // 좋아요 테이블 생성
            Schema::create($likeTableName, function (Blueprint $table) : void {
                $table->increments('id')->unsigned()->comment('좋아요 아이디');
                $table->integer('post_id')->unsigned()->default(0)->comment('게시글 아이디');
                $table->string('post_type', 30)->default('POSTS')->comment('POSTS, REPLIES, ETC');
                $table->integer('member_id')->unsigned()->default(0)->comment('사용자 아이디');
                $table->dateTime('created_at')->comment('등록일');

                // 인덱스
                $table->index(['post_id'], 'index_post_id');
            });
            DB::statement("ALTER TABLE " . $likeTableName ." comment '" . $params['board_name'] . " 좋아요히스토리'");

            return [
                'success' => true
            ];
        }
        catch (Exception $e)
        {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param $board_id
     * @return void
     */
    public function getNextPostNum($board_id) {
        $postTableName = 'bl_board_' . $board_id . "_posts";
        $postNum = DB::table($postTableName)
            ->select(DB::raw('MIN(post_num) as post_num'))
            ->first();

        $nextPostNum = $postNum->post_num ?? 0;
        return $nextPostNum - 1;
    }

    public function getNextCommentNum($board_id, $post_id) {
        $postTableName = 'bl_board_' . $board_id . "_replies";
        $commentNum = DB::table($postTableName)
            ->select(DB::raw('MIN(cmt_num) as cmt_num'))
            ->where('post_id', $post_id)
            ->first();

        $nextCommentNum = $commentNum->cmt_num ?? 0;
        return $nextCommentNum - 1;
    }
}
