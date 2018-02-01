<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;

    /*
     * Define custom actions here
     */

    /**
     * @param array $data
     * @param \Illuminate\Http\UploadedFile[]|null $files
     * @return \Illuminate\Http\JsonResponse
     */
    function sendPrivateApiRequest(array $data, $files = null)
    {
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->request->set('api', json_encode($data));

        if ($files) {
            $request->files->add($files);
        }

        $controller = new \App\Http\Controllers\PrivateApiController();
        return $controller->index($request, app());
    }
}
