</main>

<footer>
    <style>
        /* Remove unwanted outline/border for mouse clicks */
        .btn-link:focus:not(:focus-visible) {
            outline: none;
            box-shadow: none;
            border: none;
        }

        /* Responsive image styles */
        .responsive-img {
            height: 150px;
            width: 158px;
            padding: 10px;
            background-color: rgb(248, 248, 255);
            border-radius: 0px;
        }

        @media screen and (max-width: 1200px) {
            .responsive-img {
                height: 123px !important;
                width: 130px !important;
            }
        }

        @media screen and (max-width: 992px) {
            .responsive-img {
                height: 104px !important;
                width: 110px !important;
            }
        }

        @media screen and (max-width: 600px) {
            .responsive-img {
                height: 104px !important;
                width: 110px !important;
            }
        }

        @media screen and (max-width: 450px) {
            .responsive-img {
                height: 81px !important;
                width: 85px !important;
            }
        }

        @media screen and (max-width: 350px) {
            .responsive-img {
                height: 65px !important;
                width: 68px !important;
            }
        }

        /* Footer institutional section */
        .footer-institutional {
            background: var(--text-primary);
            color: #c3b9ff;
            padding: 2rem 0 1rem;
            margin-top: 1rem;
        }

        .footer-institutional .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-row {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            align-items: center;
            justify-content: space-between;
        }

        .footer-logo-section {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .footer-links-section {
            flex: 1;
            min-width: 200px;
        }

        .footer-links-section a {
            color: #ffffff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .footer-links-section a:hover {
            color: #c3b9ff;
        }

        .footer-links-section i {
            margin-right: 0.5rem;
        }

        .footer-map-section {
            min-width: 200px;
        }



        .footer-copyright {
            border-top: 1px solid var(--border-color);
            margin-top: 2rem;
            padding-top: 1rem;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-copyright .copyright-text {
            color: #c3b9ff;
        }

        .footer-copyright a {
            color: #eadea6;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-copyright a:hover {
            color: #c3b9ff;
            text-decoration: underline;
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--primary-color);
            color: white !important;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 999;
            text-decoration: none;
            box-shadow: var(--shadow-lg);
            font-size: 1.2rem;
        }

        .back-to-top:hover {
            background: var(--primary-dark);
            color: white !important;
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .back-to-top i {
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .footer-institutional {
                margin-top: 0;
                padding: 1.5rem 0 1rem;
            }

            .footer-row {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .footer-logo-section {
                justify-content: center;
            }

            .footer-copyright {
                flex-direction: column;
                text-align: center;
                margin-top: 1rem;
                padding-top: 0.75rem;
            }
        }
    </style>

    <!-- Footer Institutional Section -->
    <div class="footer-institutional">
        <div class="container">
            <div class="footer-row">
                <div class="footer-logo-section">
                    <div>
                        <a href="https://icar-crri.in/" title="Visit CRRI webpage" target="_blank">
                            <img class="responsive-img" src="img/crri.jpg" alt="CRRI Logo">
                        </a>
                    </div>
                    <div>
                        <a href="https://iasri.res.in/" title="Visit IASRI webpage" target="_blank">
                            <img class="responsive-img" src="img/iasri.png" alt="IASRI Logo">
                        </a>
                    </div>
                </div>

                <div class="footer-links-section">
                    <p>
                        <a href="others/ICAR_Data_Use_Licence.pdf" target="_blank">
                            <i class="fa fa-book"></i>ICAR Data Use Licence
                        </a>
                    </p>
                    <p>
                        <a href="contact.php">
                            <i class="fa fa-envelope"></i>Contact Us
                        </a>
                    </p>
                    <p>
                        <a href="https://github.com/asifalivk007/propeg" target="_blank">
                            <i class="fa-brands fa-github" aria-hidden="true"></i>Source Code
                        </a>
                    </p>
                </div>

                <!-- Col : Visitor Globe -->
                <div class="col-lg-2 col-md-6 d-flex align-items-center justify-content-center flex-column p-0">
                  <div class="footer-map-section w-100 p-0">
                    <!-- Load 3D Engine Dependencies with explicit versions and UMD paths -->
                    <script src="https://unpkg.com/three@0.160.0/build/three.min.js"></script>
                    <script src="https://unpkg.com/globe.gl@2.32.2/dist/globe.gl.min.js"></script>
      
                    <!-- The visual placeholder frame for the footer -->
                    <div id="trafficGlobe" style="width: 100%; height: 180px; border-radius: 8px; overflow: hidden; background: transparent; margin: 0 auto; display: flex; justify-content: center; align-items: center; text-align: center;"></div>
                    <!-- Counter block directly under the globe -->
                    <div id="visitCounter" style="color: #ffffff; border: 1px solid #ffffff; border-radius: 12px; padding: 3px 10px; font-size: 11px; margin: 5px auto 0 auto; font-family: monospace, sans-serif; text-align: center; display: block; width: -moz-fit-content; width: fit-content;">Visitors: ... || Visits: ... </div>
      
                    <script>
                      // Ensure absolute path from the current directory
                      const dataUrl = '<?php echo rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/\\\\"); ?>/globe-data.php';
                      
                      Promise.all([
                        fetch(dataUrl).then(async res => {
                          if (!res.ok) throw new Error(`HTTP ${res.status}: ${await res.text()}`);
                          return res.json();
                        }),
                        // Fetch GeoJSON for drawing the countries (landmass)
                        fetch('https://raw.githubusercontent.com/vasturiano/globe.gl/master/example/datasets/ne_110m_admin_0_countries.geojson')
                          .then(res => res.json())
                      ])
                      .then(([data, countries]) => {
                        if(data.error) {
                          throw new Error("API Error: " + data.error);
                        }
                        
                        if (typeof Globe === 'undefined') {
                          throw new Error("The globe.gl library failed to load (possibly blocked by an extension or network issue).");
                        }
                        
                        // Calculate and display total visits
                        
                        document.getElementById('visitCounter').innerHTML = `<strong>Visitors:</strong> ${(data.total_visitors || 0).toLocaleString()} <strong>||</strong> <strong>Visits:</strong> ${(data.total_visits || 0).toLocaleString()}`;
                        
                        const container = document.getElementById('trafficGlobe');
                        const gWidth = container.clientWidth || 200;
                        const gHeight = container.clientHeight || 180;
                        
                        const worldGlobe = Globe()
                          (container)
                          .width(gWidth)
                          .height(gHeight)
                          .backgroundColor('rgba(0,0,0,0)') 
                          .showGlobe(true)
                          .showAtmosphere(false)
                          // Draw landmass optimized (no heavy 3D walls)
                          .polygonsData(countries.features)
                          .polygonAltitude(0.01) 
                          .polygonCapColor(() => '#000f4d') 
                          .polygonSideColor(() => 'transparent') 
                          .polygonStrokeColor(() => '#666666') 
                          // Draw traffic data as flat dots
                          .labelsData(data.globe || [])
                          .labelLat(d => d.lat)
                          .labelLng(d => d.lng)
                          .labelDotRadius(1.5) 
                          .labelColor(() => '#fff700') 
                          .labelText(() => '') 
                          .labelAltitude(0.02) 
                          .labelLabel(d => `
                            <div style="background: rgba(10,10,10,0.9); padding: 4px 8px; border-radius: 4px; border: 1px solid #333; color: #fff; font-family: monospace, sans-serif; font-size: 8px;">
                              <strong></strong> ${d.label}<strong>:</strong> ${d.weight}
                            </div>
                          `)
                          .onGlobeClick(() => window.open('https://asifalivk7analytics.duckdns.org/share/W7LWaNuEddevxWyH', '_blank'))
                          .onPolygonClick(() => window.open('https://asifalivk7analytics.duckdns.org/share/W7LWaNuEddevxWyH', '_blank'))
                          .onLabelClick(() => window.open('https://asifalivk7analytics.duckdns.org/share/W7LWaNuEddevxWyH', '_blank'))
                          .onLabelHover(label => {
                            if (label) {
                              worldGlobe.controls().autoRotateSpeed = 0; // Instantly freeze when hovered
                              if (window.globeHoverTimeout) clearTimeout(window.globeHoverTimeout);
                            } else {
                              // When mouse leaves, wait exactly 2 seconds before resuming rotation
                              if (window.globeHoverTimeout) clearTimeout(window.globeHoverTimeout);
                              window.globeHoverTimeout = setTimeout(() => {
                                worldGlobe.controls().autoRotateSpeed = 3;
                              }, 2000);
                            }
                          });
                          
                        // Set faint color via globeMaterial for the base
                        const globeMat = worldGlobe.globeMaterial();
                        globeMat.color.set('#ffffff');
                        globeMat.transparent = true;
                        globeMat.opacity = 0.15;
      
                        // Apply flat lighting to remove shadows
                        const scene = worldGlobe.scene();
                        scene.children.forEach(c => {
                          if (c.type === 'AmbientLight') c.intensity = 6;
                          if (c.type === 'DirectionalLight') c.intensity = 0;
                        });
                          
                        worldGlobe.controls().autoRotate = true;
                        worldGlobe.controls().autoRotateSpeed = 3; 
                        worldGlobe.controls().enableZoom = false; 
                        worldGlobe.controls().enablePan = false; 
                        worldGlobe.pointOfView({ altitude: 1.7 }); 
                      })
                      .catch(err => {
                        console.error('Globe Error:', err);
                        document.getElementById('trafficGlobe').innerHTML = `<p style="color:#ff6b6b; font-size: 11px; word-break: break-all;">Error: ${err.message}</p>`;
                      });
                    </script>
                  </div>
                </div>
            </div>

            <div class="footer-copyright">
                <div class="container">
                    <div class="row">
                        <p>Copyrights &copy; 2026 - All Rights Reserved.<br>
                            Designed and Developed by <a href="https://icar-crri.in/" target="_blank">ICAR-CRRI</a>,
                            Cuttack and <a href="https://iasri.res.in/" target="_blank">ICAR-IASRI</a>, New Delhi
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/isotope/isotope.pkgd.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>

    <!-- Navigation Active State Script -->
    <script>
        $(document).ready(function () {
            // Preloader functionality
            var loaderRemoved = false;
            var hideLoader = function () {
                if (loaderRemoved) return;
                loaderRemoved = true;
                $('.loader').addClass('fade-out');
                setTimeout(function () {
                    $('.loader').remove();
                }, 500);
            };

            // Hide loader when window is fully loaded
            $(window).on('load', function () {
                hideLoader();
            });

            // Fallback: hide loader after 3 seconds max to prevent hanging due to slow resources
            setTimeout(hideLoader, 3000);


            // Set active navigation state
            $(function () {
                var pgurl = window.location.href.substr(window.location.href.lastIndexOf("/") + 1);
                $("#navigation li").each(function () {
                    var link = $("a", this).attr('href');
                    if (link == pgurl) {
                        $(this).find('a').addClass('active');
                    } else if (pgurl === 'about.php' || pgurl === 'team.php') {
                        $('.about').addClass('active');
                    }
                });
            });

            // Back to top button functionality
            $('.back-to-top').click(function (e) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: 0 }, 800);
            });

            // Show/hide back to top button
            $(window).scroll(function () {
                if ($(this).scrollTop() > 100) {
                    $('.back-to-top').fadeIn();
                } else {
                    $('.back-to-top').fadeOut();
                }
            });
        });
    </script>
</footer>

<script src="js/script.js?v=2.2.0"></script>
</body>

</html>