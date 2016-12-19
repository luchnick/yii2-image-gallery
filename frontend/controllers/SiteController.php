<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use common\models\Picture;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Imagine\Image\Point;
use yii\web\UploadedFile;
use yii\helpers\Html;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $pictures = [];
        $model = new Picture();

        if (\Yii::$app->user->id) {
            $query = $model->imageQuery();
            $pictures = $query['pictures'];
            $pages = $query['pages'];

            return $this->render('index', compact('pictures', 'model', 'pages'));
        }

        return $this->render('index', compact('pictures', 'model'));
    }

    /**
     * Upload multiple images.
     *
     * @return mixed
     */
    public function actionUploadImage()
    {
        $model = new Picture();

        if (Yii::$app->request->isAjax) {
            $model->imageFiles = UploadedFile::getInstances($model, 'imageFiles');
            if ($model->validate()) {
                if ($model->upload()) {
                    $query = $model->imageQuery();
                    $pictures = $query['pictures'];
                    $pages = $query['pages'];

                    return $this->renderPartial('content/table', compact('pictures', 'pages'));
                }
            }

        }

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        return $model->getErrors();
    }

    /**
     * Rotate image.
     *
     * @return mixed
     */
    public function actionRotateImage()
    {
        $id = \Yii::$app->request->post('id');

        $model = Picture::findOne($id);
        $path = $model->getImage(false);

        $model->imageRotate($path);

        $viewPath = '/' . $path . '?' . time();

        return Html::img($viewPath, ['height' => 200]);
    }

    /**
     * Delete image.
     *
     * @return mixed
     */
    public function actionDeleteImage()
    {
        $id = \Yii::$app->request->post('id');
        $model = Picture::findOne($id);

        $path = $model->getImage(false);
        unlink($path);

        $model->delete();

        $query = $model->imageQuery();
        $pictures = $query['pictures'];
        $pages = $query['pages'];

        return $this->renderPartial('content/table', compact('pictures', 'pages'));
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
