<?php

class Gallery_model extends CI_Model
{
    private const STORAGE_DIR = 'writable/uploads/server-gallery';
    private const STORAGE_FILE = 'writable/uploads/server-gallery/gallery.json';

    public function getAll(): array
    {
        $items = $this->read();

        usort($items, static function (array $left, array $right) {
            $leftSort = (int) ($left['sort_order'] ?? 9999);
            $rightSort = (int) ($right['sort_order'] ?? 9999);

            if ($leftSort === $rightSort) {
                return (int) ($left['id'] ?? 0) <=> (int) ($right['id'] ?? 0);
            }

            return $leftSort <=> $rightSort;
        });

        return array_map(fn (array $item) => $this->normalize($item), $items);
    }

    public function find(int $id): ?array
    {
        foreach ($this->read() as $item) {
            if ((int) ($item['id'] ?? 0) === $id) {
                return $this->normalize($item);
            }
        }

        return null;
    }

    public function create(array $data): array
    {
        $items = $this->read();
        $data['id'] = $this->nextId($items);
        $items[] = $this->sanitize($data);
        $this->write($items);

        return $this->find((int) $data['id']) ?? [];
    }

    public function update(int $id, array $data): ?array
    {
        $items = $this->read();

        foreach ($items as $index => $item) {
            if ((int) ($item['id'] ?? 0) !== $id) {
                continue;
            }

            $data['id'] = $id;
            $items[$index] = $this->sanitize(array_merge($item, $data));
            $this->write($items);

            return $this->find($id);
        }

        return null;
    }

    public function delete(int $id): void
    {
        $items = array_values(array_filter($this->read(), static function (array $item) use ($id) {
            return (int) ($item['id'] ?? 0) !== $id;
        }));

        $this->write($items);
    }

    public function getUploadDirectory(): string
    {
        $this->ensureStorage();

        return FCPATH . self::STORAGE_DIR;
    }

    private function read(): array
    {
        $this->ensureStorage();

        if (!file_exists(FCPATH . self::STORAGE_FILE)) {
            return [];
        }

        $contents = file_get_contents(FCPATH . self::STORAGE_FILE);
        $items = json_decode($contents ?: '[]', true);

        return is_array($items) ? $items : [];
    }

    private function write(array $items): void
    {
        $this->ensureStorage();
        file_put_contents(
            FCPATH . self::STORAGE_FILE,
            json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function ensureStorage(): void
    {
        $dir = FCPATH . self::STORAGE_DIR;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = FCPATH . self::STORAGE_FILE;

        if (!file_exists($file)) {
            file_put_contents($file, "[]\n");
        }
    }

    private function nextId(array $items): int
    {
        $maxId = 0;

        foreach ($items as $item) {
            $maxId = max($maxId, (int) ($item['id'] ?? 0));
        }

        return $maxId + 1;
    }

    private function sanitize(array $item): array
    {
        return [
            'id' => (int) ($item['id'] ?? 0),
            'title' => trim((string) ($item['title'] ?? '')),
            'caption' => trim((string) ($item['caption'] ?? '')),
            'url' => trim((string) ($item['url'] ?? '')),
            'type' => trim((string) ($item['type'] ?? 'media')),
            'thumbnail' => trim((string) ($item['thumbnail'] ?? '')),
            'sort_order' => is_numeric($item['sort_order'] ?? null) ? (int) $item['sort_order'] : 999,
        ];
    }

    private function normalize(array $item): array
    {
        $item = $this->sanitize($item);
        $item['thumbnail_url'] = $this->normalizeUrl($item['thumbnail']);
        $item['media_url'] = $this->normalizeUrl($item['url']);

        return $item;
    }

    private function normalizeUrl(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $value)) {
            return $value;
        }

        return rtrim(base_url(), '/') . '/' . ltrim($value, '/');
    }
}
