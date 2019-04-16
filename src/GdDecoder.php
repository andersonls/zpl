<?php
namespace Zpl;

use InvalidArgumentException;

class GdDecoder
{
    /**
     * The GD image resource.
     *
     * @var resource
     */
    protected $image;
    /**
     * Create a new decoder instance.
     *
     * @param resource $image
     */
    public function __construct($image)
    {
        if (!$this->isGdResource($image)) {
            throw new InvalidArgumentException('Invalid resource');
        }
        if (!imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        $this->image = $image;
    }

    /**
     * Determine if specified image is a GD resource.
     *
     * @param mixed $image
     * @return bool
     */
    public function isGdResource($image): bool
    {
        if (is_resource($image)) {
            return get_resource_type($image) === 'gd';
        }
        return false;
    }

    /**
     * Create a new decoder instance from the specified GD resource.
     *
     * @param resource $image
     * @return GdDecoder
     */
    public static function fromResource($image): self
    {
        return new static($image);
    }

    /**
     * Create a new decoder instance from the specified file path.
     *
     * @param string $path
     * @return GdDecoder
     */
    public static function fromPath(string $path): self
    {
        return static::fromString(file_get_contents($path));
    }

    /**
     * Create a new decoder instance from the specified string.
     *
     * @param string $data
     * @return GdDecoder
     */
    public static function fromString(string $data): self
    {
        if (false === $image = imagecreatefromstring($data)) {
            throw new InvalidArgumentException('Could not read image');
        }
        return new static($image);
    }

    /**
     * Destroy the instance.
     */
    public function __destruct()
    {
        imagedestroy($this->image);
    }

    /**
     * {@inheritdoc}
     */
    public function width(): int
    {
        return imagesx($this->image);
    }
    /**
     * {@inheritdoc}
     */
    public function height(): int
    {
        return imagesy($this->image);
    }
    /**
     * {@inheritdoc}
     */
    public function getBitAt(int $x, int $y): int
    {
        return (imagecolorat($this->image, $x, $y) & 0xFF) < 127 ? 1 : 0;
    }

    /**
     * Scale the image. Leave height null to auto-maintain aspect ratio.
     *
     * @param int $width Width in dots
     * @param int $height Height in dots, leave -1 to maintain aspect ratio
     */
    public function scaleImage(int $width, ?int $height = -1)
    {
        $this->image = imagescale($this->image, $width, $height);
    }
}