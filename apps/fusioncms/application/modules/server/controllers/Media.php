<?php

use App\Config\Services;
use MX\MX_Controller;

class Media extends MX_Controller
{
    public function __construct()
    {
        $this->load->library('administrator');
        $this->load->library('upload');
        $this->load->model('gallery_model');

        parent::__construct();

        requirePermission('canViewMediaAdmin');
    }

    public function index(int|bool $editId = false)
    {
        $editing = $editId ? $this->gallery_model->find((int) $editId) : null;

        $this->administrator->setTitle('Media wall');

        $output = $this->template->loadPage('admin_media.tpl', [
            'url' => $this->template->page_url,
            'items' => $this->gallery_model->getAll(),
            'editing' => $editing,
            'notice' => Services::session()->getFlashdata('server_media_notice'),
            'error' => Services::session()->getFlashdata('server_media_error'),
        ]);

        $content = $this->administrator->box('Media wall', $output);

        $this->administrator->view($content);
    }

    public function create()
    {
        requirePermission('canManageMedia');

        $data = $this->collectPayload();

        if ($data['title'] === '' || $data['url'] === '') {
            $this->redirectWith('Title and destination URL are required.', true);
        }

        $this->gallery_model->create($data);
        $this->redirectWith('Media entry created.');
    }

    public function save(int|bool $id = false)
    {
        requirePermission('canManageMedia');

        if (!$id || !is_numeric($id)) {
            show_error('Invalid media entry.', 400);
        }

        $existing = $this->gallery_model->find((int) $id);

        if (!$existing) {
            show_error('Media entry not found.', 404);
        }

        $data = $this->collectPayload($existing);

        if ($data['title'] === '' || $data['url'] === '') {
            $this->redirectWith('Title and destination URL are required.', true);
        }

        $this->gallery_model->update((int) $id, $data);
        $this->redirectWith('Media entry updated.');
    }

    public function delete(int|bool $id = false)
    {
        requirePermission('canManageMedia');

        if (!$id || !is_numeric($id)) {
            show_error('Invalid media entry.', 400);
        }

        $this->gallery_model->delete((int) $id);
        $this->redirectWith('Media entry deleted.');
    }

    private function collectPayload(array $existing = []): array
    {
        $uploadedMedia = $this->handleMediaUpload();
        $url = trim((string) $this->input->post('url'));

        if ($uploadedMedia !== null) {
            $url = $uploadedMedia;
        } elseif ($url === '' && !empty($existing['url'])) {
            $url = $existing['url'];
        }

        $thumbnail = trim((string) $this->input->post('thumbnail'));

        $uploadedThumbnail = $this->handleThumbnailUpload();
        if ($uploadedThumbnail !== null) {
            $thumbnail = $uploadedThumbnail;
        } elseif ($thumbnail === '' && !empty($existing['thumbnail'])) {
            $thumbnail = $existing['thumbnail'];
        } elseif ($thumbnail === '' && $uploadedMedia !== null && $this->isImagePath($uploadedMedia)) {
            $thumbnail = $uploadedMedia;
        }

        return [
            'title' => trim((string) $this->input->post('title')),
            'caption' => trim((string) $this->input->post('caption')),
            'url' => $url,
            'type' => trim((string) $this->input->post('type')),
            'thumbnail' => $thumbnail,
            'sort_order' => trim((string) $this->input->post('sort_order')),
        ];
    }

    private function handleMediaUpload(): ?string
    {
        if (empty($_FILES['media_upload']['name'])) {
            return null;
        }

        $config = [
            'upload_path' => $this->gallery_model->getUploadDirectory(),
            'allowed_types' => 'gif|jpg|jpeg|png|webp|mp4|webm',
            'encrypt_name' => true,
            'max_size' => 51200,
        ];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('media_upload')) {
            $this->redirectWith(strip_tags($this->upload->display_errors('', '')), true);
        }

        $data = $this->upload->data();

        return 'writable/uploads/server-gallery/' . $data['file_name'];
    }

    private function handleThumbnailUpload(): ?string
    {
        if (empty($_FILES['thumbnail_upload']['name'])) {
            return null;
        }

        $config = [
            'upload_path' => $this->gallery_model->getUploadDirectory(),
            'allowed_types' => 'gif|jpg|jpeg|png|webp',
            'encrypt_name' => true,
            'max_size' => 5120,
        ];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('thumbnail_upload')) {
            $this->redirectWith(strip_tags($this->upload->display_errors('', '')), true);
        }

        $data = $this->upload->data();

        return 'writable/uploads/server-gallery/' . $data['file_name'];
    }

    private function redirectWith(string $message, bool $error = false): void
    {
        if ($error) {
            Services::session()->setFlashdata('server_media_error', $message);
        } else {
            Services::session()->setFlashdata('server_media_notice', $message);
        }

        redirect($this->template->page_url . 'server/media');
    }

    private function isImagePath(string $path): bool
    {
        return (bool) preg_match('/\.(gif|jpe?g|png|webp)$/i', $path);
    }
}
