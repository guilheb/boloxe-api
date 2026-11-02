import type { Env } from '../env';
import { Flickr } from './flickr';

export interface Album {
    id: string;
    title: string;
    description: string | null;
    photos: Photo[];
}

export interface Photo {
    url: string;
    title: string | null;
    height: number;
    width: number;
}

export class Photos {
    private readonly flickr: Flickr;
    private readonly userId: string;

    constructor(env: Env) {
        this.flickr = new Flickr({
            consumerKey: env.FLICKR_API_KEY,
            consumerSecret: env.FLICKR_API_SECRET,
            accessToken: env.FLICKR_OAUTH_ACCESS_TOKEN,
            accessTokenSecret: env.FLICKR_OAUTH_ACCESS_TOKEN_SECRET,
        });
        this.userId = env.FLICKR_USER_NSID;
    }

    async fetch(): Promise<Album[]> {
        const albums = await this.getAlbums();

        // One subrequest per album: keep their number under the Workers plan's limit
        await Promise.all(albums.map(async album => {
            album.photos = await this.getPhotos(album.id);
        }));

        return albums;
    }

    private async getAlbums(): Promise<Album[]> {
        const data = await this.call('flickr.photosets.getList', {
            user_id: this.userId,
            primary_photo_extras: 'last_update,url_m',
        });

        return data.photosets.photoset.map((photoSet: any): Album => ({
            id: photoSet.id,
            title: photoSet.title._content,
            description: photoSet.description._content,
            photos: [],
        }));
    }

    private async getPhotos(albumId: string): Promise<Photo[]> {
        const data = await this.call('flickr.photosets.getPhotos', {
            photoset_id: albumId,
            user_id: this.userId,
            extras: 'geo,o_dims,last_update,url_l',
        });

        return data.photoset.photo.map((photo: any): Photo => ({
            url: photo.url_l,
            title: photo.title,
            height: Number(photo.height_l),
            width: Number(photo.width_l),
        }));
    }

    private async call(method: string, parameters: Record<string, string>): Promise<Record<string, any>> {
        let data: Record<string, any>;

        try {
            data = await this.flickr.call(method, parameters);
        }
        catch (e) {
            throw new Error(`Flickr HTTP request ${method} failed: ${e instanceof Error ? e.message : e}`);
        }

        // stat = ok / fail
        if (data.stat === 'fail') {
            throw new Error(`Flickr ${method} failed: ${data.message ?? 'unknown error'}`);
        }

        return data;
    }
}
