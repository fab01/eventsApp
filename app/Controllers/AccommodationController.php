<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 29/04/18
 * Time: 12:58
 */

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Accommodation;
use Respect\Validation\Validator as v;

class AccommodationController extends Controller
{
    //==== READ

    /**
    * @param $request
    * @param $response
    *
    * @return mixed
    *
    * Select All accommodations
    */
    public function getAll($request, $response)
    {
        $accommodation = new Accommodation();
        return $this->view->render($response, 'controller/accommodation/all.html.twig', ['accommodations' => Accommodation::all()]);
    }

    //==== CREATE

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Create a new accommodation. Get Form.
     */
    public function getAccommodationCreate($request, $response)
    {
        $form = $this->form->getFields('Accommodation')->createSet();

        return $this->view->render($response, 'controller/accommodation/manage.html.twig',
          [
            'form_title'  => 'Create new accommodation',
            'form_submit' => 'Create accommodation',
            'form_action' => 'accommodation.create',
            'form' => $form,
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Create a new accommodation. On form submit.
     */
    public function postAccommodationCreate($request, $response)
    {
        $validation = $this->validator->validate($request,
          [
            'title' => v::notEmpty(),
          ]
        );

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('accommodation.create'));
        }

        $event = Accommodation::create([
          'title'  => $request->getParam('title'),
        ]);

        $this->flash->addMessage('success', "Accommodation {$event->title} has been correctly created!");

        return $response->withRedirect($this->router->pathFor('accommodation.all'));
    }

    //==== UPDATE

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update accommodation. Get Form.
     */
    public function getAccommodationUpdate($request, $response, $args)
    {
        $form = $this->form->getFields('Accommodation')->updateSet($args['id']);

        return $this->view->render($response, 'controller/accommodation/manage.html.twig',
          [
            'form_title'  => 'Update accommodation',
            'form_submit' => 'Save',
            'form_action' => 'accommodation.update',
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
     * Update accommodation. On form submit.
     */
    public function postAccommodationUpdate($request, $response)
    {
        $params = [
          'id' => v::notEmpty(),
          'title' => v::notEmpty(),
        ];

        $validation = $this->validator->validate($request, $params);
        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('accommodation.update', ['id' => $request->getParam('id')]));
        }

        Accommodation::where('id', $request->getParam('id'))->update(
          [
            'title'  => $request->getParam('title')
          ]
        );

        $this->flash->addMessage('success', "Accommodation has been correctly updated!");
        return $response->withRedirect($this->router->pathFor('accommodation.all'));
    }

    //==== DELETE

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update event. Get Form.
     */
    public function getAccommodationDelete($request, $response, $args)
    {
        if (!Auth::isAdmin()) {
            $this->flash->addMessage('error', "You don't have permission to delete an event!");
            return $response->withRedirect($this->router->pathFor('meetup.all.eid'));
        }
        else {
            Accommodation::find($args['id'])->delete();
            $this->flash->addMessage('success', "Accommodation deleted!");
            return $response->withRedirect($this->router->pathFor('event.all'));
        }
    }
}
