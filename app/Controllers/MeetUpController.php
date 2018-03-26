<?php

namespace App\Controllers;

use App\Models\MeetUp;
use Respect\Validation\Validator as v;

class MeetUpController extends Controller
{
    //==== READ
    public function getAll($request, $response)
    {
        $events = $this->form->getFields('Event')->selectAll();
        return $this->view->render($response, 'controller/meetup/all.html.twig', ['events' => $events]);
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     */
    public function postAll($request, $response)
    {
        $meetups = MeetUp::where('event_id', $request->getParam('events'))->orderBy('date')->get();
        $events = $this->form->getFields('Event')->selectAll($request->getParam('events'));

        return $this->view->render($response, 'controller/meetup/all.html.twig', ['events' => $events, 'meetups' => $meetups]);
    }

    //==== CREATE
    public function getMeetUpCreate($request, $response)
    {
        $form = $this->form->getFields('MeetUp')->createSet();

        return $this->view->render($response, 'controller/meetup/manage.html.twig',
          [
            'form_title'  => 'Crea nuova Tavola Rotonda',
            'form_submit' => 'Crea Tavola Rotonda',
            'form_action' => 'meetup.create',
            'form' => $form,
          ]
        );
    }

    public function postMeetUpCreate($request, $response) {
        $validation = $this->validator->validate($request,
          [
            'title' => v::notEmpty(),
            'date' => v::notEmpty()->date('d-m-Y'),
            'events' => v::notEmpty(),
          ]
        );

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('meetup.create'));
        }

        $meetup = MeetUp::create([
          'title'  => $request->getParam('title'),
          'date' => date_format(date_create($request->getParam('date')), 'Y-m-d H:i:s'),
          'description' => $request->getParam('description'),
          'event_id' => $request->getParam('events'),
        ]);

        $this->flash->addMessage('success', "Meetup {$meetup->title} has been correctly created!");

        return $response->withRedirect($this->router->pathFor('meetup.all'));
    }

    //==== UPDATE

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update event. Get Form.
     */
    public function getMeetUpUpdate($request, $response, $args)
    {
        $form = $this->form->getFields('MeetUp')->updateSet($args['id']);

        return $this->view->render($response, 'controller/meetup/manage.html.twig',
          [
            'form_title'  => 'Update MeetUp',
            'form_submit' => 'Save',
            'form_action' => 'meetup.update',
            'form' => $form,
            'id' => $args['id'],
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update meet up. On form submit.
     */
    public function postMeetUpUpdate($request, $response)
    {
        $params = [
          'id' => v::notEmpty(),
          'title' => v::notEmpty(),
          'date' => v::notEmpty()->date('d-m-Y'),
          'events' => v::notEmpty(),
        ];
        $toUpdate = [
          'title'  => $request->getParam('title'),
          'date' => date_format(date_create($request->getParam('date')), 'Y-m-d H:i:s'),
          'description' => $request->getParam('description'),
          'event_id' => $request->getParam('events')
        ];

        $validation = $this->validator->validate($request, $params);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('meetup.update', ['id' => $request->getParam('id')]));
        }

        MeetUp::where('id', $request->getParam('id'))->update($toUpdate);

        $this->flash->addMessage('success', "Meetup has been correctly updated!");

        return $response->withRedirect($this->router->pathFor('meetup.all'));
    }

    //==== DELETE

}