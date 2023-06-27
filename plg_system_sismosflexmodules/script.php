<?php
/**
* @package     Joomla.Plugins
* @subpackage  System.Sismosflexmodules
*
* @author      Martina Scholz <martina@simplysmart-it.de>
* @copyright   (C) 2023 Martina Scholz, SimplySmart-IT <https://simplysmart-it.de>
* @license     GNU General Public License version 3 or later; see LICENSE.txt
* @link        https://simplysmart-it.de
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since 1.0.0
 */
class PlgSystemSismosflexmodulesInstallerScript extends InstallerScript
{
	/**
	 * Minimum supported Joomla! version
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $minimumJoomla = '4.0.0';

	/**
	 * Minimum supported PHP version
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $minimumPhp = '7.4.0';

	/**
	 * Function called after extension installation/update/removal procedure commences.
	 *
	 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0.0
	 */
	public function postflight(string $type, InstallerAdapter $adapter)
	{
		if ($type != 'install' && $type != 'discover_install') {
			return true;
		}

		$lang = Factory::getApplication()->getLanguage();
		$lang->load('plg_system_sismosflexmodules', JPATH_ADMINISTRATOR);

		Factory::getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SISMOSFLEXMODULES_POSTINSTALL_MSG'));

		return true;
	}
}
