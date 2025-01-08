<?php

namespace API\Photos;

use API\Photos\Entities\Album;
use API\Photos\Entities\Photo;
use Exception;

class Photos
{
    private FlickrAPI $flickr;

    public function __construct()
    {
        $this->flickr = new FlickrAPI($_ENV['FLICKR_API_KEY'], $_ENV['FLICKR_API_SECRET']);
        $this->flickr->setOauthData(FlickrAPI::OAUTH_ACCESS_TOKEN, $_ENV['FLICKR_OAUTH_ACCESS_TOKEN']);
        $this->flickr->setOauthData(FlickrAPI::OAUTH_ACCESS_TOKEN_SECRET, $_ENV['FLICKR_OAUTH_ACCESS_TOKEN_SECRET']);
        $this->flickr->setOauthData(FlickrAPI::USER_NSID, $_ENV['FLICKR_USER_NSID']);
        $this->flickr->setOauthData(FlickrAPI::USER_NAME, $_ENV['FLICKR_USER_NAME']);
        $this->flickr->setOauthData(FlickrAPI::USER_FULL_NAME, $_ENV['FLICKR_USER_FULL_NAME']);
    }

    /**
     * @return Album[]
     * @throws Exception
     */
    public function fetch(): array
    {
        $albums = $this->getAlbums();

        foreach ($albums as $album) {
            foreach ($this->getPhotos($album->id) as $photo) {
                $album->addPhoto($photo);
            }
        }

        return $albums;
    }

    /**
     * @return Album[]
     * @throws Exception
     */
    private function getAlbums(): array
    {
        $data = $this->flickr->call('flickr.photosets.getList', [
            'user_id'              => $_ENV['FLICKR_USER_NSID'],
            'primary_photo_extras' => 'last_update,url_m'
        ]);

        if ($data === null) {
            throw new Exception('Flickr HTTP request flickr.photosets.getList failed');
        }

        foreach ($data['photosets']['photoset'] as $photoSet) {
            $photoSets[] = new Album(
                $photoSet['id'],
                $photoSet['title']['_content'],
                $photoSet['description']['_content']
            );
        }

        return $photoSets;
    }

    /**
     * @return Photo[]
     * @throws Exception
     */
    private function getPhotos(string $album_id): array
    {
        $data = $this->flickr->call('flickr.photosets.getPhotos', [
            'photoset_id' => $album_id,
            'user_id'     => $_ENV['FLICKR_USER_NSID'],
            'extras'      => 'geo,o_dims,last_update,url_l'
        ]);

        if ($data === null) {
            throw new Exception('Flickr HTTP request flickr.photosets.getPhotos failed');
        }
        // stat = ok / fail
        elseif ($data['stat'] == 'fail') {
            throw new Exception('Album not found');
        }

        foreach ($data['photoset']['photo'] as $photo) {
            $photos[] = new Photo(
                $photo['url_l'],
                $photo['title'],
                $photo['height_l'],
                $photo['width_l'],
            );
        }

        return $photos;
    }
}