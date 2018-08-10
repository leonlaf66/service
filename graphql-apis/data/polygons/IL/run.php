<?php
function media_url($url) {
  return $url;
}

$files = scandir(__DIR__.'/');
foreach ($files as $file) {
  if (is_file(__DIR__.'/'.$file) && strpos($file, '.php') > 0 && $file <> 'run.php') {
    $data = include(__DIR__.'/'.$file);
    $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $data = str_replace('    ', '  ', $data);
    file_put_contents(__DIR__.'/'.str_replace('.php', '.json', $file), $data);
    unlink(__DIR__.'/'.$file);
  }
}