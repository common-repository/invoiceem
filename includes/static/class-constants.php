<?php
/*!
 * Plugin constants.
 *
 * @since 1.0.0
 *
 * @package    InvoiceEM
 * @subpackage Constants
 */

if (!defined('ABSPATH'))
{
	exit;
}

/**
 * Class used to implement plugin constants.
 *
 * @since 1.0.0
 */
final class InvoiceEM_Constants
{
	/**
	 * Plugin prefixes.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const FILTER = 'filter_';
	const PREFIX = 'iem_';

	/**
	 * Plugin token.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const TOKEN = 'invoiceem';

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const VERSION = '1.0.6';

	/**
	 * Plugin option names.
	 *
	 * @since 1.0.6 Added processing option.
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const OPTION_PROCESSING = self::TOKEN . '_processing';
	const OPTION_SETTINGS = self::TOKEN . '_settings';
	const OPTION_SETTINGS_COMPANY = self::OPTION_SETTINGS . '_company';
	const OPTION_SETTINGS_GENERAL = self::OPTION_SETTINGS . '_general';
	const OPTION_SETTINGS_INVOICING = self::OPTION_SETTINGS . '_invoicing';
	const OPTION_SETTINGS_EMAIL = self::OPTION_SETTINGS . '_email';
	const OPTION_SETTINGS_TRANSLATION = self::OPTION_SETTINGS . '_translation';
	const OPTION_VERSION = self::TOKEN . '_version';

	/**
	 * Plugin setting names.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const SETTING_DELETE_ROLES = 'delete_roles';
	const SETTING_DELETE_SETTINGS = 'delete_settings';
	const SETTING_PER_PAGE = 'per_page';
	
	/**
	 * Plugin actions.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const ACTION_ACTIVATE = 'activate';
	const ACTION_ADD = 'add';
	const ACTION_BULK = 'iem-bulk';
	const ACTION_CLIENT_NOTE = 'client-note';
	const ACTION_COPY = 'copy';
	const ACTION_DEACTIVATE = 'deactivate';
	const ACTION_DELETE = 'delete';
	const ACTION_DELETE_ALL = 'iem-delete-all';
	const ACTION_EDIT = 'edit';
	const ACTION_LINE_ITEM = 'line-item';
	const ACTION_LIST = 'list';
	const ACTION_NOTE = 'note';
	const ACTION_PAYMENT_COMPLETED = 'payment-completed';
	const ACTION_PAYMENT_FAILED = 'payment-failed';
	const ACTION_RESEND = 'resend';
	const ACTION_SEND = 'send';
	const ACTION_SUBMITTED = 'submitted';
	const ACTION_VIEW = 'view';

	/**
	 * Global database column names.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const COLUMN_HISTORY = 'history';
	const COLUMN_IS_ACTIVE = 'is_active';
	const COLUMN_LOCKED = 'locked';
	const COLUMN_PREVIOUS_ID = 'previous_id';

	/**
	 * Encryption method string.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const ENCRYPTION_METHOD = 'aes-256-cbc';

	/**
	 * MySQL date and time formats.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const MYSQL_DATE = 'Y-m-d';

	/**
	 * Plugin statuses.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const STATUS_ACTIVE = 'active';
	const STATUS_ARCHIVED = 'archived';
	const STATUS_COMPLETED = 'completed';
	const STATUS_FAILED = 'failed';
	const STATUS_INACTIVE = 'inactive';
	const STATUS_OVERDUE = 'overdue';
	const STATUS_PAID = 'paid';
	const STATUS_SCHEDULED = 'scheduled';
	const STATUS_UNPAID = 'unpaid';

	/**
	 * Plugin database table names.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const TABLE_CLIENTS = 'clients';
	const TABLE_COUNTRIES = 'countries';
	const TABLE_CURRENCIES = 'currencies';
	const TABLE_INVOICES = 'invoices';
	const TABLE_PAYMENTS = 'payments';
	const TABLE_PAYMENT_INVOICES = 'payment_invoices';
	const TABLE_PROJECTS = 'projects';

	/**
	 * Plugin user capabilities.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const CAP_ADD = self::PREFIX . self::ACTION_ADD . '_';
	const CAP_ADD_CLIENTS = self::CAP_ADD . self::TABLE_CLIENTS;
	const CAP_ADD_INVOICES = self::CAP_ADD . self::TABLE_INVOICES;
	const CAP_ADD_PAYMENTS = self::CAP_ADD . self::TABLE_PAYMENTS;
	const CAP_ADD_PROJECTS = self::CAP_ADD . self::TABLE_PROJECTS;
	const CAP_DELETE = self::PREFIX . self::ACTION_DELETE . '_';
	const CAP_DELETE_CLIENTS = self::CAP_DELETE . self::TABLE_CLIENTS;
	const CAP_DELETE_INVOICES = self::CAP_DELETE . self::TABLE_INVOICES;
	const CAP_DELETE_PAYMENTS = self::CAP_DELETE . self::TABLE_PAYMENTS;
	const CAP_DELETE_PROJECTS = self::CAP_DELETE . self::TABLE_PROJECTS;
	const CAP_EDIT = self::PREFIX . self::ACTION_EDIT . '_';
	const CAP_EDIT_CLIENTS = self::CAP_EDIT . self::TABLE_CLIENTS;
	const CAP_EDIT_COUNTRIES = self::CAP_EDIT . self::TABLE_COUNTRIES;
	const CAP_EDIT_CURRENCIES = self::CAP_EDIT . self::TABLE_CURRENCIES;
	const CAP_EDIT_INVOICES = self::CAP_EDIT . self::TABLE_INVOICES;
	const CAP_EDIT_PAYMENTS = self::CAP_EDIT . self::TABLE_PAYMENTS;
	const CAP_EDIT_PROJECTS = self::CAP_EDIT . self::TABLE_PROJECTS;
	const CAP_VIEW_REPORTS = self::PREFIX . 'view_reports';

	/**
	 * Plugin user role.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const ROLE_ACCOUNT_MANAGER = self::PREFIX . 'account_manager';
	
	/**
	 * Plugin hook strings.
	 *
	 * @since 1.0.6 Removed notice hook.
	 * @since 1.0.5 Added list raw and rows hooks.
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const HOOK_ACTIONS = 'actions';
	const HOOK_ASSETS = 'assets';
	const HOOK_DEFAULTS = 'defaults';
	const HOOK_DIALOGS = 'dialogs';
	const HOOK_HELP = 'help';
	const HOOK_JOIN = 'join';
	const HOOK_LIST_RAW = 'list_';
	const HOOK_META_BOXES = 'meta_boxes';
	const HOOK_NOTE = '_note';
	const HOOK_PDF = 'pdf';
	const HOOK_ROWS = 'rows';
	const HOOK_SELECT = 'select';
	const HOOK_SETTINGS = 'settings_';
	const HOOK_TABS = 'tabs';
	const HOOK_TOOLBAR = 'toolbar';
	
	/**
	 * Plugin hook prefixes.
	 *
	 * @since 1.0.5 Modified list hooks and added payment list hook.
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const HOOK_CLIENT = self::PREFIX . 'client_';
	const HOOK_EMAIL = self::PREFIX . 'email_';
	const HOOK_EXTENSION = self::PREFIX . 'extension_';
	const HOOK_FIELD = self::PREFIX . 'field_';
	const HOOK_FORM = self::PREFIX . 'form_';
	const HOOK_INVOICE = self::PREFIX . 'invoice_';
	const HOOK_INVOICE_LIST = self::HOOK_INVOICE . self::HOOK_LIST_RAW;
	const HOOK_LIST = self::PREFIX . self::HOOK_LIST_RAW;
	const HOOK_OBJECT = self::PREFIX . 'object_';
	const HOOK_OBJECT_ROW = self::HOOK_OBJECT . 'row_';
	const HOOK_PAYMENT = self::PREFIX . 'payment_';
	const HOOK_PAYMENT_LIST = self::HOOK_PAYMENT . self::HOOK_LIST_RAW;
	const HOOK_SELECT2 = self::PREFIX . 'select2_';
	const HOOK_TRANSLATION = self::PREFIX . 'translation_';
	
	/**
	 * Plugin hook names.
	 *
	 * @since 1.0.6 Removed upgrade notice hooks.
	 * @since 1.0.5 Removed unused hooks and added reporting hooks.
	 * @since 1.0.4 Removed unused hook.
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const HOOK_ACCOUNTING = self::PREFIX . 'accounting';
	const HOOK_ADD_LINE_ITEM = self::PREFIX . 'add_line_item';
	const HOOK_ADD_NOTE = self::PREFIX . 'add' . self::HOOK_NOTE;
	const HOOK_CLIENT_ASSETS = self::HOOK_CLIENT . self::HOOK_ASSETS;
	const HOOK_CLIENT_HELP = self::HOOK_CLIENT . self::HOOK_HELP;
	const HOOK_CLIENT_LIMIT = self::HOOK_CLIENT . 'limit';
	const HOOK_CLIENT_META_BOXES = self::HOOK_CLIENT . self::HOOK_META_BOXES;
	const HOOK_DAILY = self::PREFIX . 'daily';
	const HOOK_EMAIL_DEFAULTS = self::HOOK_EMAIL . self::HOOK_DEFAULTS;
	const HOOK_EMAIL_HEADERS = self::HOOK_EMAIL . 'headers';
	const HOOK_EMAIL_SETTINGS_HELP = self::HOOK_EMAIL . self::HOOK_SETTINGS . self::HOOK_HELP;
	const HOOK_EMAIL_TABS = self::HOOK_EMAIL . self::HOOK_TABS;
	const HOOK_EMAIL_TO = self::HOOK_EMAIL . 'to';
	const HOOK_EMAIL_WILDCARDS = self::HOOK_EMAIL . 'wildcards';
	const HOOK_EXTENSION_CLIENTS_PLUS = self::HOOK_EXTENSION . 'clients_plus';
	const HOOK_EXTENSION_INVOICES_PLUS = self::HOOK_EXTENSION . 'invoices_plus';
	const HOOK_EXTENSION_PAYMENTS_PLUS = self::HOOK_EXTENSION . 'payments_plus';
	const HOOK_EXTENSION_PROJECTS_PLUS = self::HOOK_EXTENSION . 'projects_plus';
	const HOOK_EXTENSION_REGIONAL_PLUS = self::HOOK_EXTENSION . 'regional_plus';
	const HOOK_EXTENSION_REPORTING_PLUS = self::HOOK_EXTENSION . 'reporting_plus';
	const HOOK_FORM_OPTIONS = self::HOOK_FORM . 'options';
	const HOOK_HISTORY = self::PREFIX . 'history';
	const HOOK_INLINE_CONTENT = self::PREFIX . 'inline_content';
	const HOOK_INVOICE_ACTIONS = self::HOOK_INVOICE . self::HOOK_ACTIONS;
	const HOOK_INVOICE_ASSETS = self::HOOK_INVOICE . self::HOOK_ASSETS;
	const HOOK_INVOICE_ATTACHMENTS = self::HOOK_INVOICE . 'attachments';
	const HOOK_INVOICE_HELP = self::HOOK_INVOICE . self::HOOK_HELP;
	const HOOK_INVOICE_LIST_ASSETS = self::HOOK_INVOICE_LIST . self::HOOK_ASSETS;
	const HOOK_INVOICE_LIST_DIALOGS = self::HOOK_INVOICE_LIST . self::HOOK_DIALOGS;
	const HOOK_INVOICE_LIST_HELP = self::HOOK_INVOICE_LIST . self::HOOK_HELP;
	const HOOK_INVOICE_MARK_SENT = self::HOOK_INVOICE . 'mark_sent';
	const HOOK_INVOICE_META_BOXES = self::HOOK_INVOICE . self::HOOK_META_BOXES;
	const HOOK_INVOICE_PDF = self::HOOK_INVOICE . self::HOOK_PDF;
	const HOOK_INVOICE_POST_STATE = self::HOOK_INVOICE . 'post_state';
	const HOOK_INVOICE_ROWS = self::HOOK_INVOICE . self::HOOK_ROWS;
	const HOOK_INVOICE_TOOLBAR = self::HOOK_INVOICE . self::HOOK_TOOLBAR;
	const HOOK_LICENSE_KEY = self::PREFIX . 'license_key';
	const HOOK_LIST_ACTIONS = self::HOOK_LIST . self::HOOK_ACTIONS;
	const HOOK_LIST_ACTIVE_LABEL = self::HOOK_LIST . 'active_label';
	const HOOK_LIST_ADD_VIEWS = self::HOOK_LIST . 'add_views';
	const HOOK_LIST_DIALOGS = self::HOOK_LIST . self::HOOK_DIALOGS;
	const HOOK_LIST_INACTIVE_LABEL = self::HOOK_LIST . 'inactive_label';
	const HOOK_LIST_JOIN = self::HOOK_LIST . self::HOOK_JOIN;
	const HOOK_LIST_ORDER = self::HOOK_LIST . 'order';
	const HOOK_LIST_SELECT = self::HOOK_LIST . self::HOOK_SELECT;
	const HOOK_LIST_WHERE = self::HOOK_LIST . 'where';
	const HOOK_LOADED = self::PREFIX . 'loaded';
	const HOOK_OBJECT_JOIN = self::HOOK_OBJECT . self::HOOK_JOIN;
	const HOOK_OBJECT_SELECT = self::HOOK_OBJECT . self::HOOK_SELECT;
	const HOOK_PAYMENT_ACTIONS = self::PREFIX . self::HOOK_PAYMENT . self::HOOK_ACTIONS;
	const HOOK_PAYMENT_LIST_ASSETS = self::HOOK_PAYMENT_LIST . self::HOOK_ASSETS;
	const HOOK_PAYMENT_LIST_HELP = self::HOOK_PAYMENT_LIST . self::HOOK_HELP;
	const HOOK_PAYMENT_PDF = self::HOOK_PAYMENT . self::HOOK_PDF;
	const HOOK_PAYMENT_ROWS = self::HOOK_PAYMENT . self::HOOK_ROWS;
	const HOOK_PAYMENT_TOOLBAR = self::PREFIX . self::HOOK_PAYMENT . self::HOOK_TOOLBAR;
	const HOOK_REPORTING = self::PREFIX . 'reporting';
	const HOOK_REPORTING_ASSETS = self::HOOK_REPORTING . '_' . self::HOOK_ASSETS;
	const HOOK_REPORTING_META_BOXES = self::HOOK_REPORTING . '_' . self::HOOK_META_BOXES;
	const HOOK_REPORTING_PDF = self::HOOK_REPORTING . '_' . self::HOOK_PDF;
	const HOOK_REPORTING_TOOLBAR = self::HOOK_REPORTING . '_' . self::HOOK_TOOLBAR;
	const HOOK_SELECT2_CLIENTS = self::HOOK_SELECT2 . self::TABLE_CLIENTS;
	const HOOK_SELECT2_COUNTRIES = self::HOOK_SELECT2 . self::TABLE_COUNTRIES;
	const HOOK_SELECT2_CURRENCIES = self::HOOK_SELECT2 . self::TABLE_CURRENCIES;
	const HOOK_SELECT2_INVOICES = self::HOOK_SELECT2 . self::TABLE_INVOICES;
	const HOOK_SELECT2_PROJECTS = self::HOOK_SELECT2 . self::TABLE_PROJECTS;
	const HOOK_SEND_NOTE = self::PREFIX . 'send' . self::HOOK_NOTE;
	const HOOK_SETTINGS_TABS = self::PREFIX . self::HOOK_SETTINGS . self::HOOK_TABS;
	const HOOK_TRANSLATION_DEFAULTS = self::HOOK_TRANSLATION . self::HOOK_DEFAULTS;
	const HOOK_TRANSLATION_SETTINGS_HELP = self::HOOK_TRANSLATION . self::HOOK_SETTINGS . self::HOOK_HELP;
	const HOOK_TRANSLATION_TABS = self::HOOK_TRANSLATION . self::HOOK_TABS;
	const HOOK_VIEW = self::PREFIX . 'view';
	const HOOK_VIEW_LOAD = self::HOOK_VIEW . '_load';
	const HOOK_VIEW_SCRIPTS = self::HOOK_VIEW . '_scripts';
	const HOOK_VIEW_TEMPLATE = self::HOOK_VIEW . '_template';

	/**
	 * Plugin nonce names.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const IFRAME_NONCE = self::PREFIX . 'iframe_nonce';
	const NONCE = self::PREFIX . 'nonce';
	
	/**
	 * Plugin notice classes.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const NOTICE = 'notice-';
	const NOTICE_ERROR = self::NOTICE . 'error';
	const NOTICE_INFO = self::NOTICE . 'info';
	const NOTICE_SUCCESS = self::NOTICE . 'success';
	const NOTICE_WARNING = self::NOTICE . 'warning';

	/**
	 * Plugin URLs.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const URL_BASE = 'https://invoiceem.com/';
	const URL_DONATE = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F69LS3VU9LNJU&source=url';
	const URL_EXTENSIONS = self::URL_BASE . 'extensions/';
	const URL_KB = self::URL_BASE . 'kb/invoiceem/';
	const URL_SUPPORT = 'https://wordpress.org/support/plugin/invoiceem/';
	const URL_REVIEW = self::URL_SUPPORT . 'reviews/?rate=5#new-post';
	const URL_TRANSLATE = 'https://translate.wordpress.org/projects/wp-plugins/invoiceem';
}
