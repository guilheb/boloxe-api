<?php

namespace API\Photos;

use API\Photos\Entities\Album;
use API\Photos\Entities\Photo;
use Exception;
use NoDiscard;

final class Photos
{
    private readonly FlickrAPI $flickr;

    public function __construct()
    {
        $this->flickr = new FlickrAPI($_SERVER['FLICKR_API_KEY'], $_SERVER['FLICKR_API_SECRET']);
        $this->flickr->setOauthData(FlickrAPI::OAUTH_ACCESS_TOKEN, $_SERVER['FLICKR_OAUTH_ACCESS_TOKEN']);
        $this->flickr->setOauthData(FlickrAPI::OAUTH_ACCESS_TOKEN_SECRET, $_SERVER['FLICKR_OAUTH_ACCESS_TOKEN_SECRET']);
        $this->flickr->setOauthData(FlickrAPI::USER_NSID, $_SERVER['FLICKR_USER_NSID']);
        $this->flickr->setOauthData(FlickrAPI::USER_NAME, $_SERVER['FLICKR_USER_NAME']);
        $this->flickr->setOauthData(FlickrAPI::USER_FULL_NAME, $_SERVER['FLICKR_USER_FULL_NAME']);
    }

    /**
     * @return Album[]
     * @throws Exception
     */
    #[NoDiscard]
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
        $data = $this->call('flickr.photosets.getList', [
            'user_id'              => $_SERVER['FLICKR_USER_NSID'],
            'primary_photo_extras' => 'last_update,url_m',
        ]);

        return array_map(
            fn(array $photoSet) => new Album(
                id: $photoSet['id'],
                title: $photoSet['title']['_content'],
                description: $photoSet['description']['_content'],
            ),
            $data['photosets']['photoset'],
        );
    }

    /**
     * @return Photo[]
     * @throws Exception
     */
    private function getPhotos(string $albumId): array
    {
        $data = $this->call('flickr.photosets.getPhotos', [
            'photoset_id' => $albumId,
            'user_id'     => $_SERVER['FLICKR_USER_NSID'],
            'extras'      => 'geo,o_dims,last_update,url_l',
        ]);

        return array_map(
            fn(array $photo) => new Photo(
                url: $photo['url_l'],
                title: $photo['title'],
                height: $photo['height_l'],
                width: $photo['width_l'],
            ),
            $data['photoset']['photo'],
        );
    }

    /**
     * @throws Exception
     */
    private function call(string $method, array $parameters): array
    {
        $data = $this->flickr->call($method, $parameters);

        if ($data === null) {
            throw new Exception("Flickr HTTP request $method failed");
        }
        // stat = ok / fail
        elseif ($data['stat'] === 'fail') {
            throw new Exception("Flickr $method failed: " . ($data['message'] ?? 'unknown error'));
        }

        return $data;
    }
}
