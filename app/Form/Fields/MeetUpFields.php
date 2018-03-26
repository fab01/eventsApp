<?php

namespace App\Form\Fields;

use App\Models\MeetUp;
use App\Form\Form as Form;
use FormManager\Builder as F;

use App\Form\Fields\EventFields as EventFields;

class MeetUpFields extends Form
{
    public function createSet()
    {
        $title = F::text()->attr('name', 'title')
          ->val($this->getMessage('title'))
          ->addClass('form-control')
          ->label('Titolo della Tavola Rotonda');

        $description = F::textarea()->removeAttr('id')
          ->attr([
            'name' => 'description',
            'id' => 'description',
          ])
          ->val($this->getMessage('description'))
          ->addClass('form-control')
          ->label('Descrizione');

        $date = F::text()->attr(
          [
            'name' => 'date',
            'placeholder' => 'Data Meetup',
          ])
          ->addClass('js-datepicker')
          ->addClass('form-control');

        $events_list = new EventFields($this->form);
        $events = $events_list->embeddedList();

        $fields = [
          'title' => $title,
          'description' => $description,
          'events' => $events,
          'date' => $date,
        ];

        return $fields;
    }

    public function updateSet($id)
    {
        $data = MeetUp::find($id);

        $title = F::text()->attr('name', 'title')
          ->val($data->title)
          ->addClass('form-control')
          ->label('Titolo della Tavola Rotonda');

        $description = F::textarea()->removeAttr('id')
          ->val($data->description)
          ->attr([
            'name' => 'description',
            'id' => 'description',
          ])
          ->val($this->getMessage('description'))
          ->addClass('form-control')
          ->label('Descrizione');



        $date = F::text()->attr(
          [
            'name' => 'date',
            'placeholder' => 'Data Meetup',
          ])
          ->val(date_format(date_create($data->date),"d/m/Y"))
          ->addClass('js-datepicker')
          ->addClass('form-control');

        $events_list = new EventFields($this->form);
        $events = $events_list->embeddedList($data->event_id);

        // UPDATE - Hidden ID
        $meetUpId = F::Hidden()->attr(['name' => 'id'])->val($id);

        $fields = [
          'title' => $title,
          'description' => $description,
          'events' => $events,
          'date' => $date,
          'id' => $meetUpId,
        ];

        return $fields;
    }
}