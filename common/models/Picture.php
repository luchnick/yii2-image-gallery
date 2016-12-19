<?php

namespace common\models;

use Yii;
use yii\web\UploadedFile;
use Imagine\Gd\Imagine;
use Imagine\Image\Point;
use Imagine\Image\Box;
use yii\web\Response;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "pictures".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $image
 *
 * @property User $user
 */
class Picture extends ActiveRecord
{
    /**
     * @inheritdoc
     */

    /**
     * @var UploadedFile[]
     */
    public $imageFiles;

    public static function tableName()
    {
        return 'pictures';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['user_id', 'image'], 'required'],
            [['user_id'], 'integer'],
            [['image'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['imageFiles'], 'image', 'minWidth' => Yii::$app->params['imageWidth'],
                'minHeight' => 250, 'extensions' => 'png, jpg', 'maxFiles' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'image' => 'Image',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getImage($view = true)
    {
        $beginPath = $view ? '/' : '';
        $endPath = $view ?  '?' . time() : '';
        return $beginPath . Yii::$app->params['uploadPath'] . $this->user_id . '/' . $this->image . $endPath;
    }

    public function upload()
    {
        if ($this->validate()) {
            $path = Yii::$app->params['uploadPath'] . Yii::$app->user->id .'/';

            if(!file_exists($path)){
                mkdir($path);
            }

            $fileName = uniqid();

            foreach ($this->imageFiles as $file) {
                $pathToImage = $path . $fileName . '.' . $file->extension;
                $file->saveAs($pathToImage);

                $this->imageResize($pathToImage, Yii::$app->params['imageWidth'], Yii::$app->params['watermark']);

                $model = new Picture();
                $model->user_id = Yii::$app->user->id;
                $model->image = $fileName . '.' . $file->extension;
                $model->save();

                $response = Yii::$app->response;
                $response->format = Response::FORMAT_JSON;

                return true;
            }
        } else {
            return false;
        }
        return false;
    }

    public function imageResize($pathToImage, $width, $watermarkFile = 'images/watermark.png')
    {
        $imagine = new Imagine();

        $img = $imagine->open($pathToImage);
        $size = $img->getSize();
        $ratio = $size->getWidth()/$size->getHeight();
        $height = round($width/$ratio);
        $box = new Box($width, $height);

        $img->resize($box)->save($pathToImage);
        $imgResize = $imagine->open($pathToImage);
        $sizeResize = $imgResize->getSize();

        if ($watermarkFile) {
            $watermark = $imagine->open($watermarkFile);
            $wSize = $watermark->getSize();
            if (($sizeResize->getWidth() - $wSize->getWidth()) > 0
                && ($sizeResize->getHeight() - $wSize->getHeight()) > 0) {
                $bottomRight = new Point($sizeResize->getWidth() - $wSize->getWidth(),
                    $sizeResize->getHeight() - $wSize->getHeight());
                $imgResize->paste($watermark, $bottomRight);
                $imgResize->save($pathToImage);
            }
        }
    }

    public function imageRotate($pathToImage)
    {
        $imagine = new Imagine();

        $img = $imagine->open($pathToImage);
        $img->rotate(90)->save($pathToImage);
    }

    public function imageQuery()
    {
        $query = Picture::find()
            ->where(['user_id' => \Yii::$app->user->id])
            ->orderBy('id DESC');
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 5]);
        $pages->route = 'site/index';
        $pictures = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return compact('pictures', 'pages');
    }
}