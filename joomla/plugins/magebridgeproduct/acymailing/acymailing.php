<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Product Plugin for Acymailing
 *
 * @package MageBridge
 */
class plgMageBridgeProductAcymailing extends MageBridgePluginProduct
{
	/**
	 * Deprecated variable to migrate from the original connector-architecture to new Product Plugins
	 */
	protected $connector_field = 'acymailing_list';

	/**
	 * Method to check whether this connector is enabled or not
	 *
	 * @param null
	 * @return bool
	 */
	public function isEnabled()
	{
		if ($this->checkComponent('com_acymailing') == false) {
			return false;
		}

		if (!include_once(JPATH_ADMINISTRATOR.'/components/com_acymailing/helpers/helper.php')){
			return false;
		}

		return true;
	}

	/**
	 * Event "onMageBridgeProductPurchase"
	 * 
	 * @access public
	 * @param array $actions
	 * @param object $user Joomla! user object
	 * @param tinyint $status Status of the current order
	 * @param string $sku Magento SKU
	 */
	public function onMageBridgeProductPurchase($actions = null, $user = null, $status = null, $sku = null)
	{
		// Make sure this event is allowed
		if($this->isEnabled() == false) {
			return false;
		}

		// Check for the usergroup ID
		if(!isset($actions['acymailing_list'])) {
			return false;
		}

		// Make sure it is not empty
		$list_id = (int)$actions['acymailing_list'];
		if(!$list_id > 0) {
			return false;
		}

		// See if the user exists in the database
		$acyUser = null;
		$acyUser->email = $user->email;
		$acyUser->name = $user->name;
		$acyUser->userid = $user->id;

		$subscriberClass = acymailing::get('class.subscriber');
		$subscriberClass->checkVisitor = false;
		$subid = $subscriberClass->save($acyUser);

		if (empty($subid)) return false;
		if (empty($list_id)) return true;

		$newSubscription = array();

		$newList = null;
		$newList['status'] = 1;
		$newSubscription[intval($list_id)] = $newList;

		$subscriberClass->saveSubscription($subid, $newSubscription);

		return true;
	}

	/**
	 * Event "onMageBridgeProductReverse"
	 * 
	 * @param array $actions
	 * @param JUser $user
	 * @param string $sku Magento SKU
	 * @return bool
	 */
	public function onMageBridgeProductReverse($actions = null, $user = null)
	{
		// Make sure this event is allowed
		if($this->isEnabled() == false) {
			return false;
		}

		// Check for the usergroup ID
		if(!isset($actions['acymailing_list'])) {
			return false;
		}

		// Make sure it is not empty
		$list_id = (int)$actions['acymailing_list'];
		if(!$list_id > 0) {
			return false;
		}

		$subscriberClass = acymailing::get('class.subscriber');
		$subid = $subscriberClass->get($user->id);

		$newSubscription = array();

		$newList = null;
		$newList['status'] = 0;
		$newSubscription[intval($list_id)] = $newList;

		$subscriberClass->saveSubscription($subid,$newSubscription);

		return true;
	}
}


