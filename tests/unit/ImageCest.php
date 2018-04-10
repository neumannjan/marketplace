<?php


/**
 * Image handling CEST
 */
class ImageCest
{
    const JPG_IMG = 'img.jpg';
    const PNG_IMG = 'img.png';

    public function _before(UnitTester $I)
    {
        $user = factory(\App\User::class)->create();
        $I->amLoggedAs($user);
    }

    public function _after(UnitTester $I)
    {
    }

    protected function _getDirLength($dir)
    {
        return count(\Illuminate\Support\Facades\Storage::files($dir));
    }

    // tests

    /**
     * @param UnitTester $I
     * @param array      $images
     *
     * @throws Exception
     */
    public function offerSaveAndDelete(UnitTester $I, $images = [self::JPG_IMG])
    {
        $dir = \App\Image::STORAGE_DIR;

        //set absolute paths
        foreach ($images as $key => $image) {
            $images[$key] = \Illuminate\Http\UploadedFile::fake()->image($image,
                400, 400);
        }

        if (count($images) === 1) {
            $images = $images[0];
        }

        $lengthStart = $this->_getDirLength($dir);

        $response = $I->sendPrivateApiRequest([
            'offer-create' => [
                'name' => 'abc',
                'description' => '',
                'price' => 0,
                'currency' => 0,
                'status' => \App\Offer::STATUS_AVAILABLE,
            ],
        ], [
            'images' => $images,
        ]);

        $data = $response->getData(true);
        $data = $data['offer-create'];

        $I->assertEquals(true, $data['success']);

        $id = $data['result']['id'];

        $offer = \App\Offer::find($id);
        $I->assertNotEmpty($offer);

        $I->assertNotEmpty($offer->images);

        $I->assertGreaterThan($lengthStart, $this->_getDirLength($dir));

        // delete now

        $offer->delete();

        $I->assertEquals($lengthStart, $this->_getDirLength($dir));
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function offerSaveAndDeletePNG(UnitTester $I)
    {
        $this->offerSaveAndDelete($I, [self::PNG_IMG]);
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function offerSaveAndDeleteMultiple(UnitTester $I)
    {
        $this->offerSaveAndDelete($I, [self::JPG_IMG, self::PNG_IMG]);
    }

}
