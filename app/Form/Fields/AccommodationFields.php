<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 29/04/18
 * Time: 16:05
 */

namespace App\Form\Fields;

use App\Form\Form;
use App\Models\Accommodation;
use FormManager\Builder as F;

class AccommodationFields extends Form
{
    /**
     * @return array
     */
    public function createSet()
    {
        // CREATE - Title
        $title = F::text()->attr('name', 'title')
          ->val($this->getMessage('title'))
          ->addClass('form-control')
          ->label('Title of the event');

        $fields = [
          'title' => $title,
        ];

        return $fields;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function updateSet($id)
    {
        $data = Accommodation::find($id);
        // UPDATE - Title
        $title = F::text()->attr('name', 'title')
          ->val($data->title)
          ->addClass('form-control')
          ->label('Title of the event');

        // UPDATE - Hidden ID
        $eventId = F::Hidden()->attr(['name' => 'id'])->val($id);

        $fields = [
          'title' => $title,
          'id' => $eventId,
        ];

        return $fields;
    }

    /**
     * @param null $id
     *
     * @return array
     */
    public function selectAll( $id = null )
    {
        $data = Accommodation::All();
        $options = array();
        $options[] = '-';
        foreach ($data as $accommodation) {
            $options[$accommodation->id] = $accommodation->title;
        }

        $list = F::select()->attr('name', 'accommodations')
          ->options($options)
          ->val($this->getMessage('accommodations'))
          ->addClass('form-control');

        if (null !== $id)
            $list->val($id);

        $fields = ['accommodations' => $list];

        return $fields;
    }

    /**
     * @param null $id
     *
     * @return mixed
     */
    public function embeddedList($id = null)
    {
        $list = $this->selectAll($id);
        $accommodations = $list['accommodations']->label('<i class="fa fa-bed" aria-hidden="true"></i> Select an accommodation from the list');

        return $accommodations;
    }
}
