<?php
namespace app\controllers;

use app\models\User;
use app\models\LoginForm;
use app\models\AccountActivation;
use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use app\models\SignupForm;
use app\models\ContactForm;
use yii\helpers\Html;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Yii;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;

/**
 * Site controller.
 * It is responsible for displaying static pages, logging users in and out,
 * sign up and account activation, and password reset.
 */
class SiteController extends Controller
{
    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
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
     * Declares external actions for the controller.
     *
     * @return array
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

//------------------------------------------------------------------------------------------------//
// STATIC PAGES
//------------------------------------------------------------------------------------------------//

    /**
     * Displays the index (home) page.
     * Use it in case your home page contains static content.
     *
     * @return string
     */
    public function actionIndex()
    {

        if(Yii::$app->user->identity->unit_id) $unit_id = Yii::$app->user->identity->unit_id;
        if(Yii::$app->user->identity->sub_unit_id) $sub_unit_id = Yii::$app->user->identity->sub_unit_id;

        $totalCount = Yii::$app->db->createCommand("
        SELECT COUNT(a.cs_id) FROM
        (
            SELECT a.cs_id, a.cs_no, a.cs_tgl, a.maksud_perjalanan, a.beban_instansi
            FROM
            ta_cs a
            where
            a.unit_id::text LIKE :unit_id and a.sub_unit_id::text LIKE :sub_unit_id
            ORDER BY date_insert DESC   
        ) a     
        ", [
            ':unit_id' => isset($unit_id) ? $unit_id : '%',
            ':sub_unit_id' => isset($sub_unit_id) ? $sub_unit_id : '%',
        ])->queryScalar();

        $dataProvider = new SqlDataProvider([
            // 'db' => 'dbcs',
            'sql' => "
                SELECT a.cs_id, a.cs_no, a.cs_tgl, a.maksud_perjalanan, a.beban_instansi,
                b.time as tim, c.time as proglap, d.time as keu, e.time as ppk, f.time as kpa, g.time as spd
                FROM
                ta_cs a
                left join ( 
                    select a.ta_cs_id, a.time from ta_cs_progress a where tahapan = 1
                )b on a.id = b.ta_cs_id
                left join ( 
                    select a.ta_cs_id, a.time from ta_cs_progress a where tahapan = 2
                )c on a.id = c.ta_cs_id
                left join ( 
                    select a.ta_cs_id, a.time from ta_cs_progress a where tahapan = 3
                )d on a.id = d.ta_cs_id
                left join ( 
                    select a.ta_cs_id, a.time from ta_cs_progress a where tahapan = 4
                )e on a.id = e.ta_cs_id
                left join ( 
                    select a.ta_cs_id, a.time from ta_cs_progress a where tahapan = 5
                )f on a.id = f.ta_cs_id
                left join ( 
                    select a.ta_cs_id, a.time from ta_cs_progress a where tahapan = 6
                )g on a.id = g.ta_cs_id
                where
                a.unit_id::text LIKE :unit_id and a.sub_unit_id::text LIKE :sub_unit_id
                ORDER BY date_insert DESC            
            ",
            'params' => [
                ':unit_id' => isset($unit_id) ? $unit_id : '%',
                ':sub_unit_id' => isset($sub_unit_id) ? $sub_unit_id : '%',
            ],
            'totalCount' => $totalCount,
            //'sort' =>false, to remove the table header sorting
            'pagination' => [
                'pageSize' => 50,
            ],
        ]); 

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays the about static page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Displays the contact static page and sends the contact email.
     *
     * @return string|\yii\web\Response
     */
    public function actionContact()
    {
        $model = new ContactForm();

        if (!$model->load(Yii::$app->request->post()) || !$model->validate()) {
            return $this->render('contact', ['model' => $model]);
        }

        if (!$model->sendEmail(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'There was some error while sending email.'));
            return $this->refresh();
        }

        Yii::$app->session->setFlash('success', Yii::t('app', 
            'Thank you for contacting us. We will respond to you as soon as possible.'));
        
        return $this->refresh();
    }

    public function actionSubunit() {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            $list = \app\models\RefSubUnit::find()->select(['sub_unit_id AS id', 'nama_sub_unit AS name'])->andWhere(['unit_id'=>$id])->asArray()->all();
            $selected  = null;
            if ($id != null && count($list) > 0) {
                $selected = '';
                foreach ($list as $i => $account) {
                    $out[] = ['id' => $account['id'], 'name' => $account['name']];
                    if ($i == 0) {
                        $selected = $account['id'];
                    }
                }
                // Shows how you can preselect a value
                echo Json::encode(['output' => $out, 'selected'=>$selected]);
                return;
            }
        }
        echo Json::encode(['output' => '', 'selected'=>'']);
    }    

//------------------------------------------------------------------------------------------------//
// LOG IN / LOG OUT / PASSWORD RESET
//------------------------------------------------------------------------------------------------//

    /**
     * Logs in the user if his account is activated,
     * if not, displays appropriate message.
     *
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        // user is logged in, he doesn't need to login
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // get setting value for 'Login With Email'
        $lwe = Yii::$app->params['lwe'];

        // if 'lwe' value is 'true' we instantiate LoginForm in 'lwe' scenario
        $model = $lwe ? new LoginForm(['scenario' => 'lwe']) : new LoginForm();

        // monitor login status
        $successfulLogin = true;

        // posting data or login has failed
        if (!$model->load(Yii::$app->request->post()) || !$model->login()) {
            $successfulLogin = false;
        }

        // if user's account is not activated, he will have to activate it first
        if ($model->status === User::STATUS_INACTIVE && $successfulLogin === false) {
            Yii::$app->session->setFlash('error', Yii::t('app', 
                'You have to activate your account first. Please check your email.'));
            return $this->refresh();
        } 

        // if user is not denied because he is not active, then his credentials are not good
        if ($successfulLogin === false) {
            return $this->render('login', ['model' => $model]);
        }

        // login was successful, let user go wherever he previously wanted
        return $this->goBack();
    }

    /**
     * Logs out the user.
     *
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

/*----------------*
 * PASSWORD RESET *
 *----------------*/

    /**
     * Sends email that contains link for password reset action.
     *
     * @return string|\yii\web\Response
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();

        if (!$model->load(Yii::$app->request->post()) || !$model->validate()) {
            return $this->render('requestPasswordResetToken', ['model' => $model]);
        }

        if (!$model->sendEmail()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 
                'Sorry, we are unable to reset password for email provided.'));
            return $this->refresh();
        }

        Yii::$app->session->setFlash('success', Yii::t('app', 'Check your email for further instructions.'));

        return $this->goHome();
    }

    /**
     * Resets password.
     *
     * @param  string $token Password reset token.
     * @return string|\yii\web\Response
     *
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (!$model->load(Yii::$app->request->post()) || !$model->validate() || !$model->resetPassword()) {
            return $this->render('resetPassword', ['model' => $model]);
        }

        Yii::$app->session->setFlash('success', Yii::t('app', 'New password was saved.'));

        return $this->goHome();      
    }    

//------------------------------------------------------------------------------------------------//
// SIGN UP / ACCOUNT ACTIVATION
//------------------------------------------------------------------------------------------------//

    /**
     * Signs up the user.
     * If user need to activate his account via email, we will display him
     * message with instructions and send him account activation email with link containing account activation token. 
     * If activation is not necessary, we will log him in right after sign up process is complete.
     * NOTE: You can decide whether or not activation is necessary, @see config/params.php
     *
     * @return string|\yii\web\Response
     */
    public function actionSignup()
    {  
        // get setting value for 'Registration Needs Activation'
        $rna = Yii::$app->params['rna'];

        // if 'rna' value is 'true', we instantiate SignupForm in 'rna' scenario
        $model = $rna ? new SignupForm(['scenario' => 'rna']) : new SignupForm();

        // if validation didn't pass, reload the form to show errors
        if (!$model->load(Yii::$app->request->post()) || !$model->validate()) {
            return $this->render('signup', ['model' => $model]);  
        }

        // try to save user data in database, if successful, the user object will be returned
        $user = $model->signup();

        if (!$user) {
            // display error message to user
            Yii::$app->session->setFlash('error', Yii::t('app', 'We couldn\'t sign you up, please contact us.'));
            return $this->refresh();
        }

        // user is saved but activation is needed, use signupWithActivation()
        if ($user->status === User::STATUS_INACTIVE) {
            $this->signupWithActivation($model, $user);
            return $this->refresh();
        }

        // now we will try to log user in
        // if login fails we will display error message, else just redirect to home page
    
        if (!Yii::$app->user->login($user)) {
            // display error message to user
            Yii::$app->session->setFlash('warning', Yii::t('app', 'Please try to log in.'));

            // log this error, so we can debug possible problem easier.
            Yii::error('Login after sign up failed! User '.Html::encode($user->username).' could not log in.');
        }
                      
        return $this->goHome();
    }

    /**
     * Tries to send account activation email.
     *
     * @param $model
     * @param $user
     */
    private function signupWithActivation($model, $user)
    {
        // sending email has failed
        if (!$model->sendAccountActivationEmail($user)) {
            // display error message to user
            Yii::$app->session->setFlash('error', Yii::t('app', 
                'We couldn\'t send you account activation email, please contact us.'));

            // log this error, so we can debug possible problem easier.
            Yii::error('Signup failed! User '.Html::encode($user->username).' could not sign up. 
                Possible causes: verification email could not be sent.');
        }

        // everything is OK
        Yii::$app->session->setFlash('success', Yii::t('app', 'Hello').' '.Html::encode($user->username). '. ' .
            Yii::t('app', 'To be able to log in, you need to confirm your registration. 
                Please check your email, we have sent you a message.'));
    }

/*--------------------*
 * ACCOUNT ACTIVATION *
 *--------------------*/

    /**
     * Activates the user account so he can log in into system.
     *
     * @param  string $token
     * @return \yii\web\Response
     *
     * @throws BadRequestHttpException
     */
    public function actionActivateAccount($token)
    {
        try {
            $user = new AccountActivation($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (!$user->activateAccount()) {
            Yii::$app->session->setFlash('error', Html::encode($user->username). Yii::t('app', 
                ' your account could not be activated, please contact us!'));
            return $this->goHome();
        }

        Yii::$app->session->setFlash('success', Yii::t('app', 'Success! You can now log in.').' '.
            Yii::t('app', 'Thank you').' '.Html::encode($user->username).' '.Yii::t('app', 'for joining us!'));

        return $this->redirect('login');
    }
}
