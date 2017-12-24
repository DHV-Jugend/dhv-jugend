<?php
namespace BIT\DhvJugend\Event;

use BIT\DhvJugend\Conf;
use Fum_Html_Input_Field;
use Html_Input_Type_Enum;

/**
 * @author Christoph Bessei
 */
class RegistrationForm
{
    /**
     * @var \BIT\DhvJugend\Event\RegistrationForm
     */
    protected static $obj;

    /**
     * @var array
     */
    protected $filteredEvents;

    public function __construct()
    {
        $this->filteredEvents = [
            // Sicherheitstrainings
            3515 => 'siv',
            3514 => 'siv',
            3510 => 'siv',

            // Retterpackkurs
            3511 => 'parachutePackSeminar',
            3512 => 'parachutePackSeminar',
            3513 => 'parachutePackSeminar',
        ];
    }

    public static function registerHooks()
    {
        if (is_null(static::$obj)) {
            $registrationForm = new static();
            $registrationForm->init();
            static::$obj = $registrationForm;
        }

    }

    public function init()
    {
        foreach ($this->filteredEvents as $id => $cb) {
            add_filter(
                'ems_registration_form_fields_filter_' . $id,
                [$this, 'filter_' . $cb],
                20,
                2
            );

            add_filter(
                'ems_registration_allowed_fields_' . $id,
                [$this, 'allowedFields_' . $cb]
            );
        }

        add_filter('fum_get_input_field', [$this, 'addInputFields']);
        add_filter('fum_form_after_field_validation', [$this, 'fum_form_after_field_validation'], 10, 2);
    }


    public function addInputFields($inputFields)
    {
        $inputFields = array_merge($inputFields, static::generateParachutePackSeminarFields());
        $inputFields = array_merge($inputFields, static::generateSivFields());
        return $inputFields;
    }

    /**
     * @param bool $fieldValidationResult
     * @param \Fum_Html_Form $form
     *
     * @return bool
     * @throws \Exception
     */
    public function fum_form_after_field_validation($fieldValidationResult, \Fum_Html_Form $form)
    {
        if ($form->get_unique_name() === \Fum_Conf::$fum_event_register_form_unique_name) {
            $isPedestrian = false;

            foreach ($form->get_input_fields() as $inputField) {
                if ($inputField->get_unique_name() === \Fum_Conf::$fum_input_field_aircraft) {
                    $aircraft = $inputField->get_value();
                    if ($aircraft == 'fussgaenger') {
                        $isPedestrian = true;
                    }
                }
            }

            if (!$isPedestrian) {
                // Force validation
                foreach ($form->get_input_fields() as $inputField) {
                    if ($inputField->get_unique_name() === \Fum_Conf::$fum_input_field_license_number ||
                        $inputField->get_unique_name() === \Fum_Conf::$fum_input_field_dhv_member_number) {
                        $backup = $inputField->get_required();
                        $inputField->set_required(true);
                        if (is_wp_error($inputField->validate(true))) {
                            $fieldValidationResult = false;
                        }
                        $inputField->set_required($backup);
                    }
                }
            }
        }

        return $fieldValidationResult;
    }

    /**
     * @param Fum_Html_Input_Field[] $fields
     * @param \Fum_Html_Form $form
     * @return mixed
     */
    public function filter_siv($fields, $form)
    {
        // Only gliders allowed
        foreach ($fields as $field) {
            if ($field->get_unique_name() === 'fum_aircraft') {
                $possibleValues = $field->get_possible_values();
                foreach ($possibleValues as $key => $possibleValue) {
                    if (in_array($possibleValue['value'], ['drachen', 'fussgaenger'])) {
                        unset($possibleValues[$key]);
                    }
                }
                $field->set_possible_values($possibleValues);
            }
        }

        foreach (static::generateSivFields() as $field) {
            $form->insert_input_field_before_unique_name($field, 'fum_emergency_contact_surname');
        }

        return $form->get_input_fields();
    }

    public function allowedFields_siv($fields)
    {
        foreach (static::generateSivFields() as $field) {
            $fields[] = $field->get_unique_name();
        }

        return $fields;
    }

    /**
     * @param Fum_Html_Input_Field[] $fields
     * @param \Fum_Html_Form $form
     * @return mixed
     */
    public function filter_parachutePackSeminar($fields, $form)
    {
        foreach (static::generateParachutePackSeminarFields() as $field) {
            $form->insert_input_field_before_unique_name($field, 'fum_emergency_contact_surname');
        }

        return $form->get_input_fields();
    }

    public function allowedFields_parachutePackSeminar($fields)
    {
        foreach (static::generateParachutePackSeminarFields() as $field) {
            $fields[] = $field->get_unique_name();
        }

        return $fields;
    }

    /**
     * @return Fum_Html_Input_Field[]
     */
    protected static function generateSivFields(): array
    {
        $fields = [];

        $fields[] = new Fum_Html_Input_Field(
            Conf::PREFIX . 'glider_type',
            Conf::PREFIX . 'glider_type',
            new Html_Input_Type_Enum(Html_Input_Type_Enum::TEXT),
            'Schirmtyp (Hersteller, Typ, Größe)',
            Conf::PREFIX . 'glider_type',
            true
        );

        $fields[] = new Fum_Html_Input_Field(
            Conf::PREFIX . 'glider_color',
            'Schirmfarbe',
            new Html_Input_Type_Enum(Html_Input_Type_Enum::TEXT),
            'Schirmfarbe',
            Conf::PREFIX . 'glider_color',
            true
        );

        $fields[] = new Fum_Html_Input_Field(
            Conf::PREFIX . 'glider_checked_until',
            Conf::PREFIX . 'glider_checked_until',
            new Html_Input_Type_Enum(Html_Input_Type_Enum::TEXT),
            'Schirmcheck gültig bis',
            Conf::PREFIX . 'glider_checked_until',
            true
        );

        $fields[] = (new Fum_Html_Input_Field(
            Conf::PREFIX . 'number_of_flights',
            Conf::PREFIX . 'number_of_flights',
            new Html_Input_Type_Enum(Html_Input_Type_Enum::SELECT),
            'Anzahl deiner Höhenflüge',
            Conf::PREFIX . 'number_of_flights',
            true
        ))->set_possible_values(
            [
                ['title' => 'Bitte wählen', 'value' => ''],
                ['title' => '0 - 50', 'value' => '0-50'],
                ['title' => '51 - 100', 'value' => '51-100'],
                ['title' => '101 - 500', 'value' => '101-500'],
                ['title' => '501 oder mehr', 'value' => '> 501'],
            ]
        );

        $fields[] = (new Fum_Html_Input_Field(
            Conf::PREFIX . 'number_of_flight_hours',
            Conf::PREFIX . 'number_of_flight_hours',
            new Html_Input_Type_Enum(Html_Input_Type_Enum::SELECT),
            'Anzahl deiner Flugstunden',
            Conf::PREFIX . 'number_of_flight_hours',
            true
        ))->set_possible_values(
            [
                ['title' => 'Bitte wählen', 'value' => ''],
                ['title' => '0 - 50', 'value' => '0-50'],
                ['title' => '51 - 100', 'value' => '51-100'],
                ['title' => '101 - 500', 'value' => '101-500'],
                ['title' => '501 oder mehr', 'value' => '> 501'],
            ]
        );

        $fields[] = (new Fum_Html_Input_Field(
            Conf::PREFIX . 'number_of_siv',
            Conf::PREFIX . 'number_of_siv',
            new Html_Input_Type_Enum(Html_Input_Type_Enum::SELECT),
            'Wie viele Sicherheitstrainings hast du bereits besucht?',
            Conf::PREFIX . 'number_of_siv',
            true
        ))->set_possible_values(
            [
                ['title' => 'Bitte wählen', 'value' => ''],
                ['title' => '0', 'value' => '0'],
                ['title' => '1', 'value' => '1'],
                ['title' => '2', 'value' => '2'],
                ['title' => '3', 'value' => '3'],
                ['title' => '4 oder mehr', 'value' => '> 4'],
            ]
        );

        return $fields;
    }

    protected static function generateParachutePackSeminarFields()
    {
        $fields = [];
        $fields[] = (new Fum_Html_Input_Field(
            Conf::PREFIX . 'parachute_type',
            Conf::PREFIX . 'parachute_type',
            new Html_Input_Type_Enum(Html_Input_Type_Enum::SELECT),
            'Rettertyp',
            Conf::PREFIX . 'parachute_type',
            true
        ))
            ->set_possible_values(
                [
                    ['title' => 'Bitte wählen', 'value' => ''],
                    ['title' => 'Rundkappe', 'value' => 'rundkappe'],
                    ['title' => 'Sonstige', 'value' => 'sonstige'],
                ]
            );

        return $fields;
    }
}
