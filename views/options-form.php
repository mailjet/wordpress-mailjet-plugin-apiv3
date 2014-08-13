<?php
/**
 * This class allows the creation of Wordpress admin forms
 */

class Mailjet_Options_Form
{
	protected $fieldsets;
	protected $action;

	public function __construct($action = "")
	{
		$this->action = $action;
	}

	public function display()
	{
		echo '<form method="post" action="' . $this->action . '">';

		foreach ($this->fieldsets as $fieldset)
		{
			echo '<h3>' . $fieldset->getTitle() . '</h3>';

			if ($fieldset->getDescription())
				echo '<p>' . $fieldset->getDescription() . '</p>';

			$options = $fieldset->getOptions();

			if (!empty($options))
			{
				echo '<table class="form-table"><tbody></tbody>';

				foreach ($options as $option)
					$option->display();

				echo '</tbody></table>';
			}
		}

		submit_button(__('Save options', 'wp-mailjet'));
		echo '</form>';
	}

	public function addFieldset(Options_Form_Fieldset $fieldset)
	{
		$this->fieldsets[] = $fieldset;
	}

	public function setFieldsets($fieldsets)
	{
		$this->fieldsets = $fieldsets;
	}

	public function getFieldsets()
	{
		return $this->fieldsets;
	}
}

class Options_Form_Option
{
	protected $id;
	protected $label;
	protected $value;
	protected $type;
	protected $desc;
	protected $required;
	protected $options;

	public function __construct($id, $label, $type, $value = "", $desc="", $required = false, $options = null)
	{
		$this->id =		$id;
		$this->label =	$label;
		$this->type =	$type;
		$this->value =	$value;

		update_option($id, $value);

		$this->desc =		$desc;
		$this->required =	$required;
		$this->options =	$options;
	}

	public function display()
	{
		if ($this->type == 'text' || $this->type == 'email')
		{
			echo '
				<tr valign="top">
					<th scope="row"><label for="' . $this->getId() . '">' . $this->getLabel() . '</label></th>
					<td><input name="' . $this->getId().'" type="' . $this->getType() . '" id="' . $this->getId() . '" value="' . $this->getValue() . '" class="regular-text code"' . ($this->getRequired() ? 'required="required"' : '') . '></td>
				</tr>';
		}
		else if ($this->type == "checkbox")
		{
			echo '
				<tr valign="top">
					<th scope="row">' . $this->getDesc() . '</th>
					<td>
						<fieldset>
						<legend class="screen-reader-text"><span>' . $this->getLabel() . '</span></legend>
						<label for="'.$this->getId().'">
						<input name="'.$this->getId().'" type="' . $this->getType() . '" id="' . $this->getId() . '" value="1"' . (get_option($this->getId()) ? ' checked="checked"' : '').'">' . $this->getLabel() . '
						</label>
						</fieldset>
					</td>
				</tr>';
		}
		elseif ($this->type == 'select')
		{
			$sel =  '<tr valign="top">
						<th scope="row">' . $this->getDesc() . '</th>
						<td>
							<fieldset>
							<legend class="screen-reader-text"><span>' . $this->getLabel() . '</span></legend>
							<label for="' . $this->getId() . '">
							<select name="' . $this->getId() . '" type="' . $this->getType().'" id="' . $this->getId() . '">';

			if ($this->getOptions())
			{
				foreach ($this->getOptions() as $option)
				{
					$sel .= '<option value="' . $option['value'] . '"' . (get_option($this->getId()) == $option['value'] ? ' selected="selected"' : '') . '>' . $option['label'] . '</option>';
				}
			}

			$sel .= '</select>' . $this->getLabel().'
					</label>
				</fieldset>
			</td>
			</tr>';

			echo $sel;
		}
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setLabel($label)
	{
		$this->label = $label;
	}

	public function getLabel()
	{
	return $this->label;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setValue($value)
	{
		$this->value = $value;
		update_option( $this->id, $value);
	}

	public function getValue()
	{
		return get_option($this->getId());
	}

	public function setDesc($desc)
	{
		$this->desc = $desc;
	}

	public function getDesc()
	{
		return $this->desc;
	}

	public function setOptions($options)
	{
		$this->options = $options;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function setRequired($required)
	{
		$this->required = $required;
	}

	public function getRequired()
	{
		return $this->required;
	}
}

class Options_Form_Fieldset
{
	protected $title;
	protected $options;
	protected $description;

	public function __construct($title, array $options, $description = "")
	{
		$this->title =		$title;
		$this->options =	$options;
		$this->description =$description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setOptions($options)
	{
		$this->options = $options;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function getTitle()
	{
		return $this->title;
	}
}