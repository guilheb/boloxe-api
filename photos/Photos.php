<?php

namespace API\Photos;

use API\Cache;
use API\Photos\Entities\Album;
use API\Photos\Entities\Photo;

class Photos
{
    private Cache $cache;
    private FlickrAPI $flickr;

    public function __construct()
    {
        $this->flickr = new FlickrAPI($_ENV['FLICKR_API_KEY'], $_ENV['FLICKR_API_SECRET']);
        $this->flickr->setOauthData(FlickrAPI::OAUTH_ACCESS_TOKEN, $_ENV['FLICKR_OAUTH_ACCESS_TOKEN']);
        $this->flickr->setOauthData(FlickrAPI::OAUTH_ACCESS_TOKEN_SECRET, $_ENV['FLICKR_OAUTH_ACCESS_TOKEN_SECRET']);
        $this->flickr->setOauthData(FlickrAPI::USER_NSID, $_ENV['FLICKR_USER_NSID']);
        $this->flickr->setOauthData(FlickrAPI::USER_NAME, $_ENV['FLICKR_USER_NAME']);
        $this->flickr->setOauthData(FlickrAPI::USER_FULL_NAME, $_ENV['FLICKR_USER_FULL_NAME']);

        $this->cache = new Cache();
    }

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

    private function getAlbums() : array
    {
        $cacheName = 'photoSets.cache';
        $photoSets = $this->cache->get($cacheName) ?? [];

        if ($photoSets != null) {
            return $photoSets;
        }

        $data = $this->flickr->call('flickr.photosets.getList', [
            'user_id'              => $_ENV['FLICKR_USER_NSID'],
            'primary_photo_extras' => 'last_update,url_m'
        ]);

        foreach ($data['photosets']['photoset'] as $photoSet) {
            $photoSets[] = new Album(
                $photoSet['id'],
                $photoSet['title']['_content'],
                $photoSet['description']['_content']
            );
        }

        $this->cache->set($cacheName, $photoSets);

        return $photoSets;
    }

    private function getPhotos(string $album_id) : array
    {
        $cacheName = 'photos_'.$album_id.'.cache';
        $photos = $this->cache->get($cacheName) ?? [];

        if ($photos != null) {
            return $photos;
        }

        $data = $this->flickr->call('flickr.photosets.getPhotos', [
            'photoset_id' => $album_id,
            'user_id'     => $_ENV['FLICKR_USER_NSID'],
            'extras'      => 'geo,o_dims,last_update,url_l'
        ]);

        // stat = ok / fail
        if ($data['stat'] == 'fail') {
            throw new \Exception('Album not found');
        }

        foreach ($data['photoset']['photo'] as $photo) {
            $photos[] = new Photo(
                $photo['url_l'],
                $photo['title'],
                $photo['height_l'],
                $photo['width_l'],
            );
        }

        $this->cache->set($cacheName, $photos);

        return $photos;
    }
}