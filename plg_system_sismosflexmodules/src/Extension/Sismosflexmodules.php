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

namespace Joomla\Plugin\System\Sismosflexmodules\Extension;

// no direct access
// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;


class Sismosflexmodules extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    DatabaseDriver
	 * @since  1.0.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;


	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		if (\Joomla\CMS\Factory::getApplication()->isClient('site')) {
			return [
				'onAfterModuleList' => 'switchModPositionsByAlias'
			];
		}

		if (\Joomla\CMS\Factory::getApplication()->isClient('administrator')) {
			return [
				'onContentPrepareForm' => 'addAdditionalModulePositionsOption'
			];
		}

		return [];
		
	}

	/**
	 * Prepare form and add my field.
	 *
	 * @param   \Joomla\Event\Event $event
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function switchModPositionsByAlias(\Joomla\Event\Event $event)
	{
		$app = Factory::getApplication();

		if (!$app->isClient('site')) return;

		if (!($app->getDocument() instanceof \Joomla\CMS\Document\HtmlDocument)) return;

		[$modules] = $event->getArguments();
		$positionAlias = [];		
		$template = $app->getTemplate();
		$filePath = Path::clean(JPATH_SITE . '/templates/' . $template . '/templateDetails.xml');
		if (is_file($filePath)) {
			// Read the file to see if it's a valid component XML file
			$xml = simplexml_load_file($filePath);

			if ($xml !== false && ($xml->getName() == 'extension' || $xml->getName() == 'metafile')) {
				$positions = (array) $xml->positions;
				foreach($positions['position'] as $indPos => $pos) {
					$position = $xml->positions->position[$indPos];
					if (isset($position->alias)) {
						$positionAlias[$pos] = $position->alias;
					}
					$posAttribs = $position->attributes();

					if ($posAttribs && $aliasString = (string) $posAttribs['alias']) {
						$aliasses = explode(",", (string) $posAttribs['alias']);
						foreach($aliasses as $alias) {
							$positionAlias[$alias] = $pos;
						}
					}
				}
			}
		}

		foreach($modules as $module) {
			if (array_key_exists($module->position, $positionAlias)) {
				$module->position = $positionAlias[$module->position];
			}
			$modParams = new Registry($module->params);
			if (!empty($additionalPositions = $modParams->get('additional_position', []))) {
				Foreach ($additionalPositions as $index => $position) {
					$split_pos = explode(':', $position);
					if ((count($split_pos) === 2 && strtolower($split_pos[0]) === strtolower($template) && $split_pos[1] !== $module->position) || (count($split_pos) === 1 && $split_pos[0] !== $module->position)) {
						$newModule = clone $module;
						$newModule->id .= '-cl' . $index;
						$newModule->position = (count($split_pos) === 2) ? $split_pos[1] : $split_pos[0];
						$modules[] = $newModule;
					}					
				}
			}
		}

		$event->setArgument(0, $modules);
	}

	/**
	 * Prepare form and add field.
	 *
	 * @param   \Joomla\Event\Event $event
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function addAdditionalModulePositionsOption(\Joomla\Event\Event $event)
	{

		if (!\Joomla\CMS\Factory::getApplication()->isClient('administrator')) return;

		if (!(\Joomla\CMS\Factory::getApplication()->getDocument() instanceof \Joomla\CMS\Document\HtmlDocument)) return;

		/** @var Form $form  - The form to be altered*/

		[$form] = $event->getArguments();

		if (!$form instanceof Form) {
			return ;
		}

		$name = $form->getName();

		if ($name =='com_modules.module') {
			$lang = Factory::getApplication()->getLanguage();
			$lang->load('plg_system_sismosflexmodules', JPATH_ADMINISTRATOR);

			FormHelper::addFieldPrefix('Joomla\\Plugin\\System\\Sismosflexmodules\\Field');

			$form->load('
				<form>
					<fields name="params">
						<fieldset name="aditionalPos" label="PLG_SYSTEM_SISMOSFLEXMODULES_MODULE_FIELDSET">
							<field
								name="additional_position"
								type="Modulespositionadditional"
								label="PLG_SYSTEM_SISMOSFLEXMODULES_MODULE_FIELDSET_ADDITIONALPOSFIELD_LABEL"
								description="PLG_SYSTEM_SISMOSFLEXMODULES_MODULE_FIELDSET_ADDITIONALPOSFIELD_DESC"
								default=""
								client="site"
								multiple="true"								
							/>
						</fieldset>
					</fields>
				</form>');
		}

		return;
	}
}
