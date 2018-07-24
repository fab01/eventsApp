<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 06/05/18
 * Time: 22:34
 */

namespace App\Controllers;

use App\Models\Event;
use App\Models\EventSubscription;
use App\Models\Subscriber;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Respect\Validation\Exceptions\FalseValException;
use Respect\Validation\Validator as v;
use Slim\Http\UploadedFile;
use Slim\Http\Stream;

class SubscriptionController extends Controller
{

    /**
     * @param $request
     * @param $response
     * @param $args
     *
     * @return mixed
     * Render create Event form.
     */
    public function getEventSubscriptionCreate($request, $response, $args)
    {
        $event = Event::where('status', 1)->first();
        $status = (is_object($event)) ? $event->status : NULL;

        if (NULL !== $status) {
            $subscription = EventSubscription::where('subscriber_id', $_SESSION['uid'])->where('event_id', $event->id)->first();
            if (NULL !== $subscription && NULL !== $_SESSION['eid'] && $event->status == 1) {
                return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $subscription->id]));
            }
        }

        if (NULL !== $status && $status == 1) {
            $form = $this->form->getFields('EventSubscription')->createSet();

            return $this->view->render($response, 'controller/subscription/event.html.twig',
              [
                'start_date'  => $event->start_date,
                'end_date'    => $event->end_date,
                'form_title'  => 'Event registration',
                'form_submit' => 'Register me!',
                'form_action' => 'event.subscription.create',
                'form' => $form,
              ]
            );
        }
        return $response->withRedirect($this->router->pathFor('home'));
    }
    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     * Create the Event .
     */
    public function postEventSubscriptionCreate($request, $response)
    {
        $abstract      =  NULL;
        $uploadedFiles =  $request->getUploadedFiles();
        $application   =  $request->getParam('abstract_apply');
        $uploadedFile  =  $uploadedFiles['abstract_file'];
        $filename      =  $uploadedFile->getClientFilename();
        $directory     =  $this->container->get('upload_directory') . $_SESSION['eid'];

        // If user is updating file.
        if (null != $filename || $application) {

            // File extensions.
            $file_validation = v::oneOf(
              v::extension('doc'),
              v::extension('docx')
            )->validate($filename);

            // File apply for.
            $apply_validation = v::notEmpty()->validate($application);

            // Validate extensions.
            if (!$file_validation) {
                $this->validator->setCustomErrors(
                  'abstract_file',
                  'Please check the abstract file. Make sue the extension is on of the following: doc, docx'
                );
                $abstract = false;
            }

            // Validate application.
            if (!$apply_validation) {
                $this->validator->setCustomErrors(
                  'abstract_apply',
                  'Please select the application of the file.'
                );
                $abstract = false;
            }

            if ($file_validation && $apply_validation) {
                $abstract = true;
            }
        }

        // Set validators array (accommodations by default).
        $validators = [
          'accommodations' => v::notEmpty()
        ];

        // Validators for One Night field.
        $one_night_accommodations = [4, 5, 6, 7, 8];
        if (in_array($request->getParam('accommodations'), $one_night_accommodations)) {
            $event = Event::where('status', 1)->first();
            $validators['one_day'] = v::notEmpty()->between($event->start_date, $event->end_date);
        }

        // Validate form.
        $validation = $this->validator->validate($request, $validators);
        if ($validation->failed() || (!$abstract && NULL !== $abstract)) {
            $this->flash->addMessage('error', "Something went wrong with the submission. Check for the errors reported in the form.");
            return $response->withRedirect($this->router->pathFor('event.subscription.create'));
        }

        // Upload file ONLY IF user is currently uploading a file.
        if (NULL !== $abstract && TRUE === $abstract) {
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
            } else {
                $this->flash->addMessage('error', "Something went wrong with file upload.");
                return $response->withRedirect($this->router->pathFor('event.subscription.create'));
            }
        }

        // Save data into DB.
        $data_set = [
          'accommodation_id' => $request->getParam('accommodations'),
          'event_id'         => $_SESSION['eid'],
          'subscriber_id'    => $_SESSION['uid'],
          'abstract'         => $filename,
          'apply'            => $application,
        ];

        if (in_array($request->getParam('accommodations'), $one_night_accommodations)) {
            $data_set['one_night'] = date_format(
              date_create($request->getParam('one_day')),
              'Y-m-d H:i:s'
            );
        }

        $check_subscriber = EventSubscription::where('subscriber_id', $_SESSION['uid'])->where('event_id', $_SESSION['eid'])->first();

        if (NULL !== $check_subscriber) { // Avoid duplicated in case user uses 'browser back' button.
            $this->flash->addMessage('error', "You are already registered to this event.");
            return $response->withRedirect($this->router->pathFor('event.subscription.create'));
        }

        $subscription = EventSubscription::create($data_set);

        // If DB Save successful, notify via mail and redirect.
        if (NULL !== $subscription->id) {
            $this->notifyUser();
            return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $subscription->id]));
        }

        return $response->withRedirect($this->router->pathFor('event.subscription.create'));
    }

    /**
     * @param $request
     * @param $response
     * @param $args
     *
     * @return mixed
     * Render Update Event form.
     */
    public function getEventSubscriptionUpdate($request, $response, $args)
    {
        $subscription = new EventSubscription();
        $checkout = '3 Days - Full board registration.';

        $subscriptionDetails = $subscription->mySubscriptionDetails();
        if ($subscriptionDetails->one_night !== "") {
            $checkout = $this->checkInOut($subscriptionDetails->one_night, $subscriptionDetails->accommodation_id);
        }

        if ($subscription->isAuthorized($args['id'])) {
            $form = $this->form->getFields('EventSubscription')->updateSet($args['id']);

            return $this->view->render($response, 'controller/subscription/event.html.twig',
              [
                'accommodation' => $subscriptionDetails->title,
                'checkout' => $checkout,
                'form_title' => 'Event registration Update',
                'form_submit' => 'Update registration',
                'form_action' => 'event.subscription.update',
                'form' => $form,
                'id' => $args['id'],
              ]
            );
        }
        return $this->view->render($response, 'http/403.html.twig');
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     * Update the Event.
     */
    public function postEventSubscriptionUpdate($request, $response)
    {
        $uploadedFiles =  $request->getUploadedFiles();
        $application   =  $request->getParam('abstract_apply');
        $directory     =  $this->container->get('upload_directory') . $_SESSION['eid'];
        $uploadedFile  =  $uploadedFiles['abstract_file'];
        $filename      =  $uploadedFile->getClientFilename();
        $abstract      =  NULL;

        if (null != $filename || $application) {
            // File extensions.
            $file_validation = v::oneOf(
              v::extension('doc'),
              v::extension('docx')
            )->validate($filename);

            // File apply for.
            $apply_validation = v::notEmpty()->validate($application);

            // Validate extensions.
            if (!$file_validation) {
                $this->validator->setCustomErrors(
                  'abstract_file',
                  'Please check the abstract file. Make sue the extension is on of the following: doc, docx'
                );
                $abstract = false;
            }

            // Validate application.
            if (!$apply_validation) {
                $this->validator->setCustomErrors(
                  'abstract_apply',
                  'Please select the application of the file.'
                );
                $abstract = false;
            }

            if ($file_validation && $apply_validation) {
                $abstract = true;
            }
        }

        if (!$abstract) {
            $this->flash->addMessage('error', "Something went wrong with the submission. Check for the errors reported in the form.");
        }

        // Upload file ONLY IF user is currently uploading a file.
        if (NULL !== $abstract && TRUE === $abstract) {
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

                // remove old file if exists before replace with new one.
                $current_file = EventSubscription::find($request->getParam('id'));
                @unlink($directory . DIRECTORY_SEPARATOR . $current_file->abstract);

                // store data in table.
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $toUpdate = [
                  'abstract' => $filename,
                  'apply' => $request->getParam('abstract_apply'),
                ];

                EventSubscription::where('id', $request->getParam('id'))->update($toUpdate);
                $this->flash->addMessage('success', 'uploaded ' . $filename);
            }
        }

        return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $request->getParam('id')]));
    }

    /* @todo: MeetupSubscription create. */
    public function getMeetupSubscriptionCreate($request, $response, $args) {}

    public function postMeetupSubscriptionCreate($request, $response) {}

    /**
     * Moves the uploaded file to the upload directory.
     *
     * @param string $directory directory to which the file is moved
     * @param UploadedFile $uploaded file uploaded file to move
     * @return string filename of moved file
     */
    public function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $filename  = time() . '_' . str_replace(" ", "-", $uploadedFile->getClientFilename());
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    /**
     * Send email notifications on Event create.
     */
    public function notifyUser()
    {
        // Subscriber variables.
        $client_mail = $_SESSION['user']->mail;
        $client_name = $_SESSION['user']->field_first_name["und"][0]['value'];

        // @ administrator
        $sbjAdmin = "New subscriber to IIM event";
        $msgAdmin = $client_name . " will participate to the next IIM event";

        // @ cliente
        $sbjClient = "Thank you, " . $client_name;
        $msgClient = "Subscription to the next IIM Event confirmed! Please remember, you can still add or change Abstract file on your next access to the IIM Event Application!";

        $mail = new PHPMailer();

        try {
            // Mail to Admin.
            $mail->setFrom($client_mail);
            $mail->addAddress('fabriziosabato@gmail.com');
            $mail->addAddress('gabellini.davide@hsr.it');
            $mail->Subject = $sbjAdmin;
            $mail->Body    = $msgAdmin;
            $mail->send();
            $mail->clearAddresses();

            // Mail to Client (Confirmation).
            $mail->setFrom('noreply@coram-iim.it');
            $mail->addAddress($client_mail);
            $mail->Subject = $sbjClient;
            $mail->Body    = $msgClient;
            $mail->send();

            $this->flash->addMessage('success', 'Registration submitted!');
        }
        catch (Exception $e)
        {
            $this->flash->addMessage('error', 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo);
        }
    }

    public function checkInOut($date, $accommodation_id)
    {
        $singleNight = [4, 5];
        $doubleNight = [7, 8];

        if (in_array($accommodation_id, $singleNight)) {
            // +1 day.
            $myDate = new \DateTime($date);
            $checkIn = $myDate->format('d-m-Y');
            $myDate->modify('+1 day');
            return 'Check-in: ' . $checkIn . ' Check-Out: ' . $myDate->format('d-m-Y');
        }
        if (in_array($accommodation_id, $doubleNight)) {
            // +2 days.
            $myDate = new \DateTime($date);
            $checkIn = $myDate->format('d-m-Y');
            $myDate->modify('+2 day');
            return 'Check-in: ' . $checkIn . ' - Check-Out: ' . $myDate->format('d-m-Y');
        }

        return null;
    }

    public function getEmailExcel($request, $response, $args)
    {
        require_once('../../vendor/phpoffice/phpexcel/Classes/PHPExcel.php');

        $subscriptions = new EventSubscription();
        $subscribers = $subscriptions->getAll();

        $excel = new \PHPExcel();

        $sheet = $excel->setActiveSheetIndex(0);
        $i = 1;
        foreach($subscribers as $subscriber) {
            $cell = $sheet->getCell("A{$i}");
            $cell->setValue($subscriber->email);
            $i++;
        }
        $sheet->setSelectedCells('A1');
        $excel->setActiveSheetIndex(0);

        $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

        $excelFileName = '../../public/listEmailSubscribers.xlsx';
        $excelWriter->save($excelFileName);

        // For Excel2007 and above .xlsx files
        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="file.xlsx"');

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, file_get_contents($excelFileName));
        rewind($stream);

        return $response->withBody(new \Slim\Http\Stream($stream));
        /*$subscriptions = new EventSubscription();
        $subscribers = $subscriptions->getAll();
        $excel = "";
        foreach($subscribers as $subscriber) {
            $excel .= $subscriber->email . "\n";
        }

        return $response->withHeader('Content-Type', 'application/vnd.ms-excel')
         ->withHeader('Content-Disposition', 'attachment; filename="' . basename('emailList.xls') . '"');
        // all stream contents will be sent to the response*/
    }
}
