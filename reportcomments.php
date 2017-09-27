<?php
/*
Plugin Name: Report Comments
Description: Gives visitors the possibility to report inappropriate comments. Adds an additional page under comments in wp-admin, where an administrator may review all the reported comments and decide if they should be removed or not.
Version: 1.2
Author: Peter Berglund
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class ReportComments {

	private $version = '1.2';
	private $pluginPrefix = 'report_comments';
	private $pluginUrl;
	private $strings;

	public function __construct() {
		$this->pluginUrl = plugins_url(false, __FILE__);

		load_plugin_textdomain($this->pluginPrefix, false, 'reportcomments/lang/');

		$this->strings = $this->getStrings();

		if (is_admin()) {
			$this->backendInit();
		}
		$this->frontendInit();
	}

	/**
	 * Registers the reported comments page as a sub page to comments in admin.
	 */
	public function registerCommentsPage() {
		if (!($count = get_transient($this->pluginPrefix. '_count'))) {
			$count = $this->getCount();
			set_transient($this->pluginPrefix. '_count', $count, 30 * MINUTE_IN_SECONDS);
		}
		$bubble = '<span class="update-plugins count-' .$count. '"><span class="update-count">' .number_format_i18n($count). '</span></span>';
		$text = $this->strings['menu_title'] . $bubble;

		add_comments_page($this->strings['page_title'], $text, 'moderate_comments', $this->pluginPrefix . '_reported', array($this, 'commentsPage'));
	}

	/**
	 * Actions and filters for frontend.
	 */
	public function frontendInit() {
		if (get_option($this->pluginPrefix. '_members_only')) {
			if (is_user_logged_in()) {
				add_filter('comment_text', array($this, 'printReportLink'));
			}
		} else {
			add_filter('comment_text', array($this, 'printReportLink'));
		}

		add_action('wp_ajax_' .$this->pluginPrefix. '_flag', array($this, 'flagComment'));
		add_action('wp_ajax_nopriv_' .$this->pluginPrefix. '_flag', array($this, 'flagComment'));

		wp_enqueue_script($this->pluginPrefix . '_script', $this->pluginUrl. '/reportcomments.js', array('jquery'), $this->version, true);
		$translations = array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'confirm' => $this->strings['confirm']
		);
		wp_localize_script($this->pluginPrefix. '_script', 'ReportCommentsJs', $translations);
	}

	/**
	 * Actions for backend.
	 */
	public function backendInit() {
		add_action('admin_menu', array($this, 'registerCommentsPage'));
		add_action('admin_action_' .$this->pluginPrefix. '_ignore', array($this, 'ignoreReport'));
		add_action('admin_init', array($this, 'registerSettings'));
	}

	/**
	 * Sets all strings used by the plugin. Use the 'report_comments_strings' filter to modify them yourself.
	 * @return string
	 */
	public function getStrings() {
		$strings = array(
			// Title for link in the menu.
			'menu_title' => __('Reported', $this->pluginPrefix),
			// Title for the reported comments page.
			'page_title' => __('Reported comments', $this->pluginPrefix),
			// Confirm dialog on front end.
			'confirm' => __('Are you sure you want to report this comment', $this->pluginPrefix),
			// Message to show user after successfully reporting a comment.
			'report_success' => __('The comment has been reported.', $this->pluginPrefix),
			// Message to show user after reporting a comment has failed.
			'report_failed' => __('The comment has been reported.', $this->pluginPrefix),
			// Text for the link shown below each comment.
			'report' => __('Report comment', $this->pluginPrefix),
			// Text in admin for link that deems the comment OK.
			'ignore_report' => __('Comment is ok', $this->pluginPrefix),
			// Error message shown when a comment can't be found.
			'invalid_comment' => __('The comment does not exist', $this->pluginPrefix),
			// Header for settings field.
			'settings_header' => __('Report Comments Settings', $this->pluginPrefix),
			// Description for members only setting.
			'settings_members_only' => __('Only logged in users may report comments', $this->pluginPrefix)
		);

		return apply_filters('report_comments_strings', $strings);
	}

	/**
	 * Fetches comments flagged as reported and displays them in a table. 
	 */
	public function commentsPage() {
		if (!current_user_can('moderate_comments')) {
			die(__('Cheatin&#8217; uh?'));
		}

		global $wpdb;
		
		$comments = $wpdb->get_results(
			$wpdb->prepare("
				SELECT * FROM $wpdb->commentmeta 
				INNER JOIN $wpdb->comments on $wpdb->comments.comment_id = $wpdb->commentmeta.comment_id
				WHERE $wpdb->comments.comment_approved = 1 AND meta_key = %s AND meta_value = 1 LIMIT 0, 25",
				$this->pluginPrefix. '_reported')
		); 
		$count = count($comments);
		set_transient($this->pluginPrefix. '_count', $count, 1 * HOUR_IN_SECONDS);
		include('pages/comments-list.php');
	}

	/**
	 * Returns how many reported comments are in the system.
	 * @return int
	 */
	private function getCount() {
		global $wpdb;

		$comments = $wpdb->get_results(
			$wpdb->prepare("
				SELECT * FROM $wpdb->commentmeta 
				INNER JOIN $wpdb->comments on $wpdb->comments.comment_id = $wpdb->commentmeta.comment_id
				WHERE $wpdb->comments.comment_approved = 1 AND meta_key = %s AND meta_value = 1 LIMIT 0, 10", 
				$this->pluginPrefix. '_reported')
		); 
		return count($comments);
	}

	/**
	 * Flags a comment as reported. Won't flag a comment that has been flagged before and approved.
	 * @param  int $id Comment id.
	 * @return bool
	 */
	private function flag($id) {
		$value = get_comment_meta($id, $this->pluginPrefix. '_reported', true);
		if ($value < 0) {
			return false;
		}
		return add_comment_meta($id, $this->pluginPrefix. '_reported', true, true);
	}

	/**
	 * Ajax-callable function which flags a comment as reported.
	 * Dies with message to be displayed to user.
	 */
	public function flagComment() {
		$id = (int) $_POST['id'];
		if (!wp_verify_nonce($_POST['nonce'], $this->pluginPrefix. '_nonce') || $id != $_POST['id']) {
			die(__('Cheatin&#8217; uh?'));
		}

		if (get_option($this->pluginPrefix . '_members_only') && !is_user_logged_in()) {
			die(__('Cheatin&#8217; uh?'));
		}

		if (!$this->flag($id)) {
			// This may happen when the comment has been reported once, but deemed ok by an admin, or 
			// when something went wrong. Either way, we won't bother the visitor with that information
			// and we'll show the same message for both sucess and failed here by default.
			die($this->strings['report_failed']);
		} 
		die($this->strings['report_success']);
	}

	/**
	 * Constructs "report this comment" link.
	 * @return string
	 */
	private function getReportLink() {
		$commentId = get_comment_ID();
		$nonce = wp_create_nonce($this->pluginPrefix. '_nonce');
		$link = sprintf('<a href="javascript:void(0)" onclick="%s_flag(this, \'%s\', \'%s\')" class="report-comment">%s</a>',
			$this->pluginPrefix,
			$commentId,
			$nonce,
			$this->strings['report']
		);
		return $link;
	}

	/**
	 * Appends a "report this comment" link after the "reply" link below a comment.
	 */
	public function printReportLink($comment_reply_link) {
		return $comment_reply_link . '<br /><br />' . $this->getReportLink();
		return $comment_reply_link . ' ' . $this->getReportLink();
	}

	/**
	 * Unflags the comment as reported. 
	 */
	public function ignoreReport() {
		if (isset($_GET['c']) && isset($_GET['_wpnonce'])) {
			if (!wp_verify_nonce($_GET['_wpnonce'], 'ignore-report_' .$_GET['c']) || !current_user_can('moderate_comments')) {
				die(__('Cheatin&#8217; uh?'));
			}
			$id = absint($_GET['c']);
			if (!get_comment($id)) {
				die($this->strings['invalid_comment']);
			}
			// We set the meta value to -1, and by that it wont be able to be reported again.
			// Once deemed ok -> always ok.
			# todo: add this as an option (being able to report the comment again or not)
			update_comment_meta($id, $this->pluginPrefix. '_reported', -1);

			wp_redirect($_SERVER['HTTP_REFERER']); 
		}
	}

	/**
	 * Registers settings for plugin.
	 */
	public function registerSettings() {
		add_settings_section($this->pluginPrefix . '_settings', 
			$this->strings['settings_header'],
			null,
			'discussion'
		);

		add_settings_field($this->pluginPrefix . '_members_only',
			$this->strings['settings_members_only'],
			array($this, 'settingsCallback'),
			'discussion',
			$this->pluginPrefix . '_settings'
		);

		register_setting('discussion', $this->pluginPrefix. '_members_only');
	}

	/**
	 * Displays settings field
	 */
	public function settingsCallback() {
		?>
		<input name="<?php echo $this->pluginPrefix. '_members_only'; ?>" type="checkbox" <?php checked(get_option($this->pluginPrefix. '_members_only'), 'on') ?> />
		<?php
	}
}

/**
 * Initialize plugin.
 */
new ReportComments();
