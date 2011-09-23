<?php
# Settings
define('BASE_URL', 'whatever.com');
define('BASE_PATH', dir(__FILE__));

define('DB_HOST', 'localhost');
define('DB_USER', 'dbuser');
define('DB_PASS', 'p@ssw0rd');
define('DB_NAME', 'database');

define('FACEBOOK_APP_ID', 'xxxxxxxxxxxx');
define('FACEBOOK_API_SECRET', 'xxxxxxxxxxxxxxxxxxxx');
define('FACEBOOK_PERMISSIONS', 'publish_stream,user_likes,user_birthday,email,offline_access,publish_actions,user_location');

ini_set('include_path', BASE_PATH.'/lib:'.ini_get('include_path'));

# Autoload
function __autoload($class_name)
{
  include_once str_replace('_', '/', $class_name) . '.php';
}

if(function_exists("__autoload")) spl_autoload_register("__autoload");

# Actions
class mvc
{
	protected $page = false;

	public function __construct()
	{

	}

	public function get_page()
	{
		return $this->page;
	}

	public function page_index()
	{
		$user = new user();
		return array(
			'user'	=> $user->get_gateway()->api('/me'))
		);
	}

	# Main
	public function render($view, $data)
	{
		if(isset($_GET['ajax']) && $_GET['ajax'] == 'json')
		{
			echo json_encode($data);
			exit;
		}

		if(!file_exists(BASE_PATH.'/views/'.$view.'.php')) $view = '404';

		ob_start();
		include(BASE_PATH.'/views/'.$view.'.php');
		$html = ob_get_clean();

		if(isset($_GET['ajax']) && $_GET['ajax'] == 'html')
		{
			echo $html;
			exit;
		}

		return $html;
	}

	public function main()
	{
		$user = new user();
		if($user->login())
		{
			define('LOGGED_IN', 1);
			define("LOGOUT_LINK", $user->get_logout_link());
		}
		else
		{
			define("LOGIN_LINK", $user->get_login_link());
		}

		$page = 'index';
		$data = array();

		if(!empty($_GET['page'])) $page = $_GET['page'];

		$this->page = $page;

		if(method_exists($this, 'page_'.$page))
		{
			$data = call_user_func(array($this, 'page_'.$page));
		}

		return array($page, $data);
	}
}

$mvc = new mvc();
list($page, $data) = $mvc->main();
$html = $mvc->render($page, $data);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Head of the Class</title>

	<style type="text/css" media="all">
		@import url("/static/css/normalize.css");
		@import url("/static/css/base.css");
		@import url("/static/facebox/facebox.css");
		<?php if(file_exists(BASE_PATH.'/htdocs/static/css/'.$page.'.css')): ?>@import url("/static/css/<?=$page ?>.css"); <?php endif; ?>

	</style>

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script type="text/javascript" src="/static/js/base.js"></script>
	<script type="text/javascript" src="/static/facebox/facebox.js"></script>

	<?php if(file_exists(BASE_PATH.'/htdocs/static/js/'.$page.'.js')): ?>
	<script type="text/javascript" src="/static/js/<?=$page ?>.js"></script>
	<?php endif; ?>

</head>
<body>
	<div id="bin">
		<div id="main" class="<?=$mvc->get_page()?>">
			<?=$html ?>
		</div>
		<div id="footer">
			<?php if(defined('LOGGED_IN')): ?>
			<a href="<?=LOGOUT_LINK ?>">Log OUT</a>
			<?php else: ?>
			<a href="<?=LOGIN_LINK ?>">Log IN</a>
			<?php endif; ?>
		</div>
	</div>
	<!-- Facebook JS API -->
	<div id="fb-root"></div>
	<script src="http://connect.facebook.net/en_US/all.js" type="text/javascript"></script>
	<script type="text/javascript">
	FB.init({
		appId: '<?=FACEBOOK_APP_ID ?>',
		status: true, // check login status
		cookie: true, // enable cookies to allow the server to access the session
		xfbml: true, // parse XFBML
		channelURL: 'http://<?=$_SERVER["SERVER_NAME"] ?>/channel.php', // channel.html file
		oauth: true // enable OAuth 2.0
  	});
	</script>	
</body>
</html>