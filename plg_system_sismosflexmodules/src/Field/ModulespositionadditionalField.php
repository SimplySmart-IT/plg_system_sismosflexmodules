<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.Sismosflexmodules
 *
 * @copyright   Copyright (C) 2023 Martina Scholz. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

namespace Joomla\Plugin\System\Sismosflexmodules\Field;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field to load the list of template positions for modules
 *
 * @since  1.0.0
 */
class ModulespositionadditionalField extends \Joomla\Component\Modules\Administrator\Field\ModulesPositioneditField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'Modulespositionadditional';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $layout = 'joomla.form.field.modulespositionadditional';

	/**
     * Client name.
     *
     * @var    string
     * @since  1.0.0
     */
    protected $client;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since  1.0.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'client':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since  1.0.0
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'client':
                $this->$name = (string) $value;
                break;

            default:
                parent::__set($name, $value);
        }
    }

	/**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since  1.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result === true) {
            $this->client = $this->element['client'] ? (string) $this->element['client'] : 'site';
        }

        return $result;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since  1.0.0
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        $clientId  = $this->client === 'administrator' ? 1 : 0;
        $positions = HTMLHelper::_('modules.positions', $clientId, 1, $this->value);

        $data['client']    = $clientId;
        $data['positions'] = $positions;

        $renderer = $this->getRenderer($this->layout);
        $renderer->setComponent('com_modules');
        $renderer->setClient(1);
		$renderer->addIncludePaths($this->getLayoutPaths());

        return $renderer->render($data);
    }

	/**
	 * Allow to override renderer include paths in child fields
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	protected function getLayoutPaths()
	{
		$renderer = new FileLayout($this->layout);

		$renderer->setIncludePaths(parent::getLayoutPaths());	

		$renderer->addIncludePaths(JPATH_PLUGINS . '/system/sismosflexmodules/layouts');

		$paths = $renderer->getIncludePaths();

		foreach ($paths as $index => $path) {
			if (!preg_match('#\/templates\/[a-z,A-Z,0-9,\-\_\/]*html\/layouts$#', $path, $m)) {
				continue;
			}
			$overrideTemplate = array_splice($paths, $index, 1);
			array_splice($paths, 0, 0, $overrideTemplate);
			break;
		}

		return $paths;
	}
}
