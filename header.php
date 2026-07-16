<?php
/* --- Canonical / social URL -------------------------------------------------
   Hard-code the production host so every access variant (IP address, http,
   trailing slash, server name) consolidates to ONE canonical URL. */
$propeg_site   = 'https://propeg.abrl.in';
$propeg_script = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$propeg_canon  = ($propeg_script === 'index.php' || $propeg_script === '' || $propeg_script === '/') ? $propeg_site . '/' : $propeg_site . '/' . $propeg_script;
$propeg_ogimg  = $propeg_site . '/img/gepegrna-structure.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO Meta Tags -->
    <title>PROpeg | Advanced Plant Prime Editing Guide RNA Design Tool</title>
    <meta name="description" content="PROpeg — comprehensive tool for designing genomic pegRNAs with structure visualization for plant prime editing applications. Developed by ICAR-CRRI and ICAR-IASRI">
    <meta name="robots" content="<?php echo htmlspecialchars($page_robots ?? 'index, follow', ENT_QUOTES); ?>">
    <meta name="author" content="ICAR-CRRI &amp; ICAR-IASRI">

    <!-- Canonical + social preview -->
    <link rel="canonical" href="<?php echo htmlspecialchars($propeg_canon, ENT_QUOTES); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PROpeg">
    <meta property="og:title" content="PROpeg | Precision-optimized pegRNA | Advanced Plant Prime Editing Guide RNA Design Tool">
    <meta property="og:description" content="Comprehensive tool for designing genomic pegRNAs with structure visualization for plant prime editing applications. Developed by ICAR-CRRI &amp; ICAR-IASRI">
    <meta property="og:url" content="<?php echo htmlspecialchars($propeg_canon, ENT_QUOTES); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($propeg_ogimg, ENT_QUOTES); ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1500">
    <meta property="og:image:height" content="920">
    <meta property="og:image:alt" content="PROpeg g-epegRNA structure diagram">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PROpeg — Precision-optimized pegRNA design tool">
    <meta name="twitter:description" content="Comprehensive tool for designing genomic pegRNAs with structure visualization for plant prime editing applications. Developed by ICAR-CRRI &amp; ICAR-IASRI">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($propeg_ogimg, ENT_QUOTES); ?>">
    <meta property="og:locale" content="en_US">

    <!-- Structured data: scientific web application (Schema.org) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "PROpeg",
      "alternateName": "Precision-optimized pegRNA",
      "applicationCategory": "ScientificApplication",
      "operatingSystem": "Web browser",
      "url": "<?php echo htmlspecialchars($propeg_site . '/', ENT_QUOTES); ?>",
      "description": "Web-based tool for designing precision-optimized pegRNAs for plant prime editing applications, with PBS/RT modeling, PE3/PE3b nicking, linker design and efficiency predictions. Developed by ICAR-CRRI & ICAR-IASRI",
      "creator": { "@type": "Organization", "name": "ICAR-CRRI & ICAR-IASRI" },
      "offers": { "@type": "Offer", "price": "0", "priceCurrency": "USD" }
    }
    </script>

    <!-- Favicon — rounded-corner PNG (corners baked transparent), browser scales as needed -->
    <link rel="icon" type="image/png" href="img/propeg-favicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/propeg-favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/propeg-favicon.png">
    <link rel="apple-touch-icon" href="img/propeg-favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="img/propeg-favicon.png">
    <link rel="shortcut icon" href="img/propeg-favicon.png">

    <link rel="stylesheet" href="slider/css/layui.css">
    <link rel="stylesheet" href="css/styles.css?v=2.1.18">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="slider/layui.js"></script>
    <script defer src="https://asifalivk7analytics.duckdns.org/script.js" data-website-id="70a0ea41-6535-45b7-a240-4e5699f3e4c9"></script>
</head>

<body>
    <!--PreLoader-->
    <div class="loader">
        <div class="loader-inner">
            <div class="circle"></div>
        </div>
    </div>
    <!--PreLoader Ends-->

    <nav class="navbar">
        <div class="nav-container" style="position: relative;">
            <a href="https://icar.org.in/" title="Visit ICAR webpage" target="_blank" class="nav-icar-logo" style="display: flex;">
                <img src="img/icar.png" alt="ICAR Logo" style="height: 100%; width: auto; object-fit: contain; border-radius: inherit;">
            </a>
            <a href="index.php" class="nav-brand" style="text-decoration: none; color: inherit;">
                <img src="img/propeg.png" alt="PROpeg" class="propeg-logo">
                <div class="nav-logo">
                    <h2>PROpeg</h2>
                    <span><b style="color: #e3d235ff; font-weight: inherit;">Pr</b>ecision-<b
                            style="color: #e3d235ff; font-weight: inherit;">o</b>ptimized <b
                            style="color: #e3d235ff; font-weight: inherit;">peg</b>RNA</span>
                </div>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="design.php" class="nav-link">Design</a></li>
                <li><a href="tutorial.php" class="nav-link">Tutorial</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="citation.php" class="nav-link">How to Cite</a></li>
                <li><a href="team.php" class="nav-link">Team</a></li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <main>