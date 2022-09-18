<?php
define('KGYS_BASE_URL', 'http://trafik.gov.tr');
define('KGYS_URL', 'http://trafik.gov.tr/kgys-goruntuleri');
function fetch()
{

  $ch = curl_init(KGYS_URL);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $content = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  return [
    'content' => $content,
    'http' => $httpCode
  ];
}

function slug($text)
{
  // Strip html tags
  $text = strip_tags($text);
  // Replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  // Transliterate
  setlocale(LC_ALL, 'en_US.utf8');
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  // Remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);
  // Trim
  $text = trim($text, '-');
  // Remove duplicate -
  $text = preg_replace('~-+~', '-', $text);
  // Lowercase
  $text = strtolower($text);
  // Check if it is empty
  if (empty($text)) {
    return 'n-a';
  }
  // Return result
  return $text;
}

function clearSpaces($text)
{
  return trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
}
mb_language('uni');
mb_internal_encoding('UTF-8');

if (isset($_GET['video-download']) === false) {
  $fetch = fetch();

  $doc = new DOMDocument();
  @$doc->loadHTML($fetch['content']);

  $links = [];
  $tables = $doc->getElementsByTagName('table');
  foreach ($tables as $table) {
    $td = $table->getElementsByTagName('td');
    $key = 'Tarihsiz';
    foreach ($td as $tdcontent) {
      $content = $doc->saveHTML($tdcontent);


      if (clearSpaces($content) === '') {
        continue;
      }

      if (strpos($content, 'href') !== false) {

        $url = preg_match('/href=["\']?([^"\'>]+)["\']?/', $content, $match);
        $links[$key][] =
          [
            'url' => $match[1],
            'title' => clearSpaces($content)
          ];
      } else {
        if (clearSpaces($content) !== '') {
          $key = clearSpaces($content);
        }
      }
    }
  }

  if (count($links) > 0) {
    foreach ($links as $key => $link) {
      usort($link, function ($a, $b) {
        return strnatcasecmp($a['title'], $b['title']);
      });
      $links[$key] = $link;
    }
  }
} else {

  $file = $_GET['video-download'];
  $string = file_get_contents($file);

  if ($string === false) {
    echo "Could not read the file.";
  } else {
    echo $string;
  }
  die();
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KGYS Video Downloader</title>
  <meta name="author" content="@halillusion">
  <meta name="description" content="This project allows you to download grouped accident videos recorded by KGYS. Its allows you to download accident videos reflected on traffic cameras from KGYS.">
  <link href="https://bootswatch.com/5/cyborg/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Mulish', sans-serif;
    }

    .date-list {
      display: grid;
      grid-template-columns: auto auto auto;
      padding: 0.5rem;
    }

    .date-list .btn {
      margin: 0.2rem;
      font-size: 0.7rem;
    }

    .list-group {
      max-height: 65vh;
      overflow-y: auto;
      border-radius: 0.5rem;
      padding-right: 0.3rem;
    }

    .btn-light.active {
      opacity: 0.5;
    }

    a[data-download] .spinner-border {
      display: none;
    }

    a.disabled[data-download] .spinner-border {
      display: inline-block;
    }

    ::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }

    /* Track */
    ::-webkit-scrollbar-track {
      background: #222;
      border-radius: 0.6rem;
    }

    /* Handle */
    ::-webkit-scrollbar-thumb {
      border-radius: 0.6rem;
      margin: 0.2rem;
      background: #888;
    }

    /* Handle on hover */
    ::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    @media (max-width: 1200px) {
      .date-list {
        grid-template-columns: auto auto;
      }
    }

    @media (max-width: 768px) {
      .date-list {
        grid-template-columns: auto;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">KGYS Video Downloader</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor02" aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarColor02">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link active" href="#">Home</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" target="_blank" href="https://github.com/halillusion">Other Projects</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <main class="mt-5 pt-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="text-center">
            <h1>KGYS Video Downloader</h1>
            <p class="mb-0">This project allows you to download grouped accident videos recorded by KGYS.</p>
            <p>Its allows you to download accident videos reflected on traffic cameras from KGYS.</p>
          </div>
        </div>
        <?php
        if (count($links)) {
        ?>
          <div class="col-12 col-md-6">
            <div class="date-list" id="listTab" role="tablist">
              <?php foreach ($links as $key => $value) { ?>
                <a class="btn btn-light" data-bs-toggle="tab" data-bs-target="#<?php echo slug($key); ?>" role="tab" href="#" aria-controls="<?php echo slug($key); ?>" aria-selected="false">
                  <?php echo $key . ' <span class="badge text-bg-secondary">' . count($value) . '</span>'; ?>
                </a>
              <?php } ?>
            </div>
          </div>
          <div class="col-12 col-md-6">

            <div class="tab-content" id="listTabContent">
              <?php foreach ($links as $key => $value) {
                echo '
                <div class="tab-pane fade" id="' . slug($key) . '" role="tabpanel" tabindex="0">
                  <a href="#" class="btn btn-sm btn-primary w-100 my-2" data-download="' . slug($key) . '">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Download All
                  </a>
                  <div class="list-group list-group-flush">';
                foreach ($value as $value) {
                  echo '<a href="' . KGYS_BASE_URL . $value['url'] . '" data-name="' . slug($key . '-' . $value['title']) . '" class="list-group-item list-group-item-action" target="_blank">' . $value['title'] . '</a>';
                }
                echo '
                  </div>
                </div>';
              } ?>
            </div>
          <?php
        } else {
          echo '<p class="text-danger">No videos found.</p>';
        } ?>
      </div>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener(('DOMContentLoaded'), () => {
      const downloadBtns = document.querySelectorAll('a[data-download]');

      downloadBtns.forEach(async (item) => {
        item.addEventListener('click', async (e) => {
          e.preventDefault();
          e.target.classList.add('disabled');
          const listId = e.target.getAttribute('data-download');
          const downloadList = document.querySelectorAll(`#${listId} .list-group .list-group-item`);

          for (const listItem of downloadList) {
            listItem.scrollIntoView();
            await downloadUsingFetch(listItem);
          }
          e.target.classList.remove('disabled');
        });
      });
    });

    async function downloadUsingFetch(listItem) {
      listItem.classList.add('disabled')
      return new Promise(async (resolve) => {

        await fetch('/?video-download=' + encodeURI(listItem.getAttribute('href')), {
            method: 'GET',
            mode: 'no-cors',
            headers: {
              'Content-Type': 'application/octet-stream',
            },
          })
          .then(async (response) => {
            return await response.blob()
          })
          .then((blob) => {
            if (blob.size <= 24) {
              alert('Could not read the file. (' + listItem.getAttribute('data-name') + ')');
            } else {
              const url = window.URL.createObjectURL(
                new Blob([blob]),
              );
              const link = document.createElement('a');
              link.href = url;
              link.setAttribute(
                'download',
                listItem.getAttribute('data-name') + `.mp4`,
              );
              document.body.appendChild(link);
              link.click();
              link.parentNode.removeChild(link);
            }

          });

        listItem.classList.remove('disabled')
        resolve();
      });
    }

    const tabEl = document.querySelectorAll('.date-list [data-bs-toggle="tab"]')
    const list = [...tabEl].map((el) => {
      el.addEventListener('shown.bs.tab', e => {
        document.getElementById("listTabContent").scrollIntoView();
        // console.log(e.target.getAttribute())
      });
    });
  </script>
</body>

</html>