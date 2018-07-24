<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 09/05/18
 * Time: 12:34
 */

namespace app\Form\Fields;

use App\Form\Form;
use App\Models\Accommodation;
use App\Models\EventSubscription;
use FormManager\Builder as F;
use App\Form\Fields\AccommodationFields as AccommodationFields;

class EventSubscriptionFields extends Form
{
    /**
     * @return array
     */
    public function createSet()
    {
        // CREATE - Abstract file
        $abstract_file = F::file()->attr(
          [
            'name' => 'abstract_file',
            'id' => 'my-file-selector',
            'style' => 'display:none;',
            'onchange' => '$(\'#upload-file-info\').html(this.files[0].name)'
          ]
        );

        // CREATE - Abstract apply
        $abstract_apply = F::select()
          ->attr('name', 'abstract_apply')
          ->options(
            [
              '-',
              'abstract' => 'Apply as Abstract',
              'talk' => 'Apply as Talk',
            ]
          )
          ->addClass('form-control')
          ->val($this->getMessage('abstract_apply'))
          ->label('For what do you want to apply the file?');

        // CREATE - One Day accommodation date
        $one_day = F::text()->attr(
          [
            'name' => 'one_day',
            'placeholder' => 'Date for single/double night',
          ])
          ->addClass('js-datepicker')
          ->addClass('form-control');

        // CREATE - Accommodation
        $accommodations_list = new AccommodationFields($this->form);
        $accommodations = $accommodations_list->embeddedList();

        $fields = [
          'one_day' => $one_day,
          'abstract_file' => $abstract_file,
          'abstract_apply' => $abstract_apply,
          'accommodations' => $accommodations,
        ];

        return $fields;
    }

    /**
     * @return array
     */
    public function updateSet($id)
    {
        // CREATE - Abstract file
        $abstract_file = F::file()->attr(
          [
            'name' => 'abstract_file',
            'id' => 'my-file-selector',
            'style' => 'display:none;',
            'onchange' => '$(\'#upload-file-info\').html(this.files[0].name)'
          ]
        );

        // CREATE - Abstract apply
        $abstract_apply = F::select()
          ->attr('name', 'abstract_apply')
          ->options(
            [
              '-',
              'abstract' => 'Apply as Abstract',
              'talk' => 'Apply as Talk',
            ]
          )
          ->addClass('form-control')
          ->val($this->getMessage('abstract_apply'))
          ->label('For what do you want to apply the file?');

        $subscriptionId = F::Hidden()->attr(['name' => 'id'])->val($id);

        $fields = [
          'abstract_file' => $abstract_file,
          'abstract_apply' => $abstract_apply,
          'id' => $subscriptionId,
        ];

        return $fields;
    }

    /**
     * @return array
     */
    public function adminUpdateSet($id)
    {
        $data = EventSubscription::find($id);

        // CREATE - One Day accommodation date
        $one_night_default_value = ($data->one_night !== '0000-00-00 00:00:00') ? date_format(date_create($data->one_night),"d-m-Y") : '';
        $one_night = F::text()->attr(
          [
            'name' => 'one_night',
            'placeholder' => 'Date for single/double night',
          ])
          ->val($one_night_default_value)
          ->addClass('js-datepicker')
          ->addClass('form-control');

        // CREATE - Accommodation
        $accommodations_list = new AccommodationFields($this->form);
        $accommodations = $accommodations_list->embeddedList($data->accommodation_id);

        $subscriptionId = F::Hidden()->attr(['name' => 'id'])->val($id);

        $fields = [
          'one_night' => $one_night,
          'accommodations' => $accommodations,
          'id' => $subscriptionId,
        ];

        return $fields;
    }
}
