<?php
/**
 * Audio Processing Functions
 * Handles audio file processing, normalization, and quality enhancement
 */

class AudioProcessor {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    /**
     * Get audio file information
     */
    public function getAudioInfo($filePath) {
        $info = [
            'duration' => 0,
            'bitrate' => 0,
            'sample_rate' => 0,
            'channels' => 0,
            'format' => '',
            'size' => filesize($filePath)
        ];

        // In a real implementation, use getID3 or FFmpeg to get accurate info
        // This is a simplified version for demonstration

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'mp3') {
            $info['format'] = 'MP3';
            $info['bitrate'] = 192; // kbps
            $info['sample_rate'] = 44100; // Hz
            $info['channels'] = 2; // Stereo
            $info['duration'] = $this->getMp3Duration($filePath);
        } elseif ($ext === 'wav') {
            $info['format'] = 'WAV';
            $info['bitrate'] = 1411; // kbps (16-bit, 44.1kHz, stereo)
            $info['sample_rate'] = 44100; // Hz
            $info['channels'] = 2; // Stereo
            $info['duration'] = $this->getWavDuration($filePath);
        } elseif ($ext === 'ogg') {
            $info['format'] = 'OGG';
            $info['bitrate'] = 160; // kbps
            $info['sample_rate'] = 44100; // Hz
            $info['channels'] = 2; // Stereo
            $info['duration'] = $this->getOggDuration($filePath);
        }

        return $info;
    }

    /**
     * Get MP3 duration using PHP's built-in functions
     */
    private function getMp3Duration($filePath) {
        // This is a simplified approach
        // In production, use getID3 or FFmpeg for accurate results

        if (!file_exists($filePath)) {
            return 0;
        }

        // Read the last 128 bytes (ID3v1 tag)
        $fp = fopen($filePath, 'rb');
        fseek($fp, -128, SEEK_END);
        $tag = fread($fp, 128);
        fclose($fp);

        // Check for ID3v1 tag
        if (substr($tag, 0, 3) === 'TAG') {
            // ID3v1 tag doesn't contain duration, so we'll use a fallback
            return 120; // Default 2 minutes
        }

        // For actual MP3 duration, we would parse the frames
        // This is a placeholder for the actual implementation
        return 120;
    }

    /**
     * Get WAV duration
     */
    private function getWavDuration($filePath) {
        if (!file_exists($filePath)) {
            return 0;
        }

        $fp = fopen($filePath, 'rb');
        $header = fread($fp, 44);
        fclose($fp);

        // Check WAV header
        if (substr($header, 0, 4) !== 'RIFF' || substr($header, 8, 4) !== 'WAVE') {
            return 0;
        }

        // Get data chunk size
        $dataSize = $this->readLittleEndian($header, 40, 4);

        // Get sample rate (bytes 24-27)
        $sampleRate = $this->readLittleEndian($header, 24, 4);

        // Get bits per sample (bytes 34-35)
        $bitsPerSample = $this->readLittleEndian($header, 34, 2);

        // Calculate duration in seconds
        $byteRate = ($sampleRate * $bitsPerSample) / 8;
        $duration = $dataSize / $byteRate;

        return (int) $duration;
    }

    /**
     * Get OGG duration
     */
    private function getOggDuration($filePath) {
        // Simplified - in production use oggvorbis library
        return 120; // Default 2 minutes
    }

    /**
     * Read little-endian value from binary data
     */
    private function readLittleEndian($data, $offset, $length) {
        $value = 0;
        for ($i = 0; $i < $length; $i++) {
            $value += ord($data[$offset + $i]) << ($i * 8);
        }
        return $value;
    }

    /**
     * Normalize audio volume
     */
    public function normalizeAudio($inputFile, $outputFile, $targetLevel = -16) {
        // In a real implementation, this would use FFmpeg or another audio library
        // This is a placeholder for the actual implementation

        if (!file_exists($inputFile)) {
            return false;
        }

        // Check if FFmpeg is available
        if (!$this->isFFmpegAvailable()) {
            return false;
        }

        $command = "ffmpeg -i " . escapeshellarg($inputFile) . " -af \"loudnorm=I={$targetLevel}:TP=-1.5:LRA=11\" " . escapeshellarg($outputFile) . " -y";

        exec($command, $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Convert audio format
     */
    public function convertAudioFormat($inputFile, $outputFile, $format = 'mp3', $bitrate = 192) {
        if (!file_exists($inputFile)) {
            return false;
        }

        if (!$this->isFFmpegAvailable()) {
            return false;
        }

        $formatOptions = [
            'mp3' => "-c:a libmp3lame -q:a 2",
            'wav' => "-c:a pcm_s16le",
            'ogg' => "-c:a libvorbis -q:a 6"
        ];

        if (!isset($formatOptions[$format])) {
            return false;
        }

        $command = "ffmpeg -i " . escapeshellarg($inputFile) . " " . $formatOptions[$format] . " -b:a {$bitrate}k " . escapeshellarg($outputFile) . " -y";

        exec($command, $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Check if FFmpeg is available
     */
    private function isFFmpegAvailable() {
        exec('ffmpeg -version', $output, $returnVar);
        return $returnVar === 0;
    }

    /**
     * Generate waveform visualization
     */
    public function generateWaveform($audioFile, $outputFile, $width = 800, $height = 200) {
        if (!file_exists($audioFile)) {
            return false;
        }

        if (!$this->isFFmpegAvailable()) {
            return false;
        }

        // Generate waveform image using FFmpeg
        $command = "ffmpeg -i " . escapeshellarg($audioFile) . " -filter_complex \"[0:a]showwaves=s={$width}x{$height}:mode=cline:colors=white\" -frames:v 1 " . escapeshellarg($outputFile) . " -y";

        exec($command, $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Extract audio metadata
     */
    public function extractMetadata($audioFile) {
        $metadata = [
            'title' => '',
            'artist' => '',
            'album' => '',
            'year' => '',
            'genre' => '',
            'comment' => ''
        ];

        if (!file_exists($audioFile)) {
            return $metadata;
        }

        // In a real implementation, use getID3 or FFmpeg to extract metadata
        // This is a simplified version

        if (substr(strtolower($audioFile), -4) === '.mp3') {
            // Check for ID3 tags
            $fp = fopen($audioFile, 'rb');
            fseek($fp, -128, SEEK_END);
            $tag = fread($fp, 128);
            fclose($fp);

            if (substr($tag, 0, 3) === 'TAG') {
                $metadata['title'] = trim(substr($tag, 3, 30));
                $metadata['artist'] = trim(substr($tag, 33, 30));
                $metadata['album'] = trim(substr($tag, 63, 30));
                $metadata['year'] = trim(substr($tag, 93, 4));
                $metadata['comment'] = trim(substr($tag, 97, 28));
            }
        }

        return $metadata;
    }

    /**
     * Process audio file after upload
     */
    public function processUploadedAudio($filePath, $savePath) {
        $result = [
            'success' => false,
            'message' => '',
            'duration' => 0,
            'bitrate' => 0,
            'sample_rate' => 0,
            'channels' => 0,
            'format' => '',
            'waveform' => ''
        ];

        // Get audio info
        $audioInfo = $this->getAudioInfo($filePath);

        if ($audioInfo['duration'] > 0) {
            $result['success'] = true;
            $result['duration'] = $audioInfo['duration'];
            $result['bitrate'] = $audioInfo['bitrate'];
            $result['sample_rate'] = $audioInfo['sample_rate'];
            $result['channels'] = $audioInfo['channels'];
            $result['format'] = $audioInfo['format'];

            // Generate waveform
            $waveformPath = $savePath . '.png';
            if ($this->generateWaveform($filePath, $waveformPath)) {
                $result['waveform'] = $waveformPath;
            }

            // Normalize audio (optional)
            $normalizedPath = $savePath . '_normalized.' . pathinfo($savePath, PATHINFO_EXTENSION);
            if ($this->normalizeAudio($filePath, $normalizedPath)) {
                // Use normalized version
                copy($normalizedPath, $savePath);
                unlink($normalizedPath);
            }
        } else {
            $result['message'] = 'Failed to process audio file';
        }

        return $result;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}