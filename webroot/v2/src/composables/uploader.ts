import useApi from 'src/composables/api';
import {UploadResp} from 'src/models/fileUpload';

const uploadSliceSize = 1024 * 256; // 256 kb

export default function useUploader() {
  function upload(
    endpoint: string,
    file: File,
    progressCallback: (percent: number) => void
  ) {
    const uploader = new FileUploader(endpoint, file, progressCallback)
    return uploader.upload()
  }

  return {
    upload
  }
}

class FileUploader {
  private start: number;

  constructor(
    private endpoint: string,
    private file: File,
    private callback: (percent: number) => void
  ) {
    this.start = 0;
  }

  upload() {
    const stop = this.determineSliceStop();
    const slice = this.file.slice(this.start, stop);

    return this.uploadChunk(slice);
  }

  private uploadChunk(slice: Blob): Promise<void|UploadResp> {
    const payload = new FormData();
    payload.set('data', slice);
    payload.set('offset', this.start.toString());
    payload.set('filename', this.file.name);

    return useApi().post<FormData, UploadResp>(this.endpoint, payload)
      .then(resp => {
        this.start = this.start + uploadSliceSize;

        if (this.hasNextChunk()) {
          this.notify();

          return this.upload();
        } else {
          this.notify(true);

          return resp;
        }
      });
  }

  private determineSliceStop() {
    const fullSlice = this.start + uploadSliceSize;
    const fileSize = this.file.size;

    return fullSlice > fileSize ? fileSize : fullSlice;
  }

  private hasNextChunk() {
    return this.start < this.file.size;
  }

  private notify(complete = false) {
    const progress = complete ? 1 : this.start / this.file.size;

    this.callback(progress);
  }
}
