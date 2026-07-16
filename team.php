<?php include 'header.php'; ?>

<style>
    /* Team Page Enhanced Styles */
    .team-section {
        padding: 1rem 0;
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        min-height: calc(100vh - 70px);
    }

    .team-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .team-header {
        text-align: center;
        margin-bottom: 4rem;
        position: relative;
    }

    .team-header::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        border-radius: 2px;
    }

    .team-header h1 {
        font-size: 3.5rem;
        color: var(--text-primary);
        margin-bottom: 1rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .team-header p {
        font-size: 1.3rem;
        color: var(--text-secondary);
        max-width: 900px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .team-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        margin-bottom: 4rem;
    }

    .team-row.final-row {
        grid-template-columns: repeat(4, 1fr);
        max-width: 1400px;
        margin: 0 auto 2rem;
    }

    .team-row.final-row .team-card {
        grid-column: span 1;
    }

    .team-row.final-row .team-card:first-child {
        grid-column: 2;
    }

    .team-row.final-row .team-card:last-child {
        grid-column: 3;
    }

    .team-card {
        background: var(--bg-primary);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        transition: box-shadow 0.2s ease, border-color 0.1s ease;
        border: 2px solid transparent;
        position: relative;
    }

    .team-card:hover {
        box-shadow: var(--shadow-xl);
        border-color: var(--primary-color);
    }

    .team-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(57, 0, 153, 0.1), transparent);
        transition: left 0.3s ease;
        z-index: 1;
    }

    .team-card:hover::before {
        left: 100%;
    }

    .team-image-container {
        position: relative;
        overflow: hidden;
        height: 280px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .team-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease;
    }

    .team-card:hover .team-image {
        transform: scale(1.1);
    }

    .team-social {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        opacity: 0;
        transition: all 0.15s ease;
    }

    .team-card:hover .team-social {
        opacity: 1;
        bottom: 25px;
    }

    .social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-color);
        border-radius: 50%;
        text-decoration: none;
        transition: all 0.15s ease;
        backdrop-filter: blur(10px);
        border: 2px solid transparent;
    }

    .social-btn:hover {
        background: var(--primary-color);
        color: white;
        transform: scale(1.2) rotate(360deg);
        border-color: white;
    }

    .team-info {
        padding: 2rem;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .team-name {
        font-size: 1.25rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .team-role {
        font-size: 1rem;
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .team-division {
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }

    .team-institute {
        font-size: 0.85rem;
        color: var(--accent-color);
        font-weight: 500;
    }

    .section-divider {
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--primary-color), var(--secondary-color), var(--primary-color), transparent);
        margin: 3rem 0;
        border-radius: 2px;
        position: relative;
    }

    .section-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        background: var(--secondary-color);
        border-radius: 50%;
        border: 3px solid var(--bg-primary);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .team-row {
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .team-header h1 {
            font-size: 3rem;
        }
    }

    @media (max-width: 992px) {
        .team-row {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .team-row.final-row {
            grid-template-columns: repeat(2, 1fr);
        }

        .team-row.final-row .team-card:first-child {
            grid-column: 1;
        }

        .team-row.final-row .team-card:last-child {
            grid-column: 2;
        }

        .team-header h1 {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 768px) {

        .team-row,
        .team-row.final-row {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .team-row.final-row .team-card {
            max-width: none;
        }

        .team-row.final-row .team-card:first-child,
        .team-row.final-row .team-card:last-child {
            grid-column: 1;
        }

        .team-section {
            padding: 2rem 0;
        }

        .team-header h1 {
            font-size: 2rem;
        }

        .team-card,
        .team-row.final-row .team-card {
            margin: 0 1rem;
            width: auto;
        }

        .team-image-container {
            height: 250px;
        }
    }

    @media (max-width: 480px) {
        .team-container {
            padding: 0 15px;
        }

        .team-header {
            margin-bottom: 2rem;
        }

        .team-info {
            padding: 1.5rem;
        }

        .team-card,
        .team-row.final-row .team-card {
            margin: 0;
        }
    }
</style>

<!-- Team Section -->
<section id="team" class="section active">
    <div class="team-section">
        <div class="team-container">
            <div class="team-header">
                <h1>Our Research Team</h1>
                <p>Meet the dedicated scientists, researchers, and developers behind PROpeg, working together to advance
                    plant genome editing technology and crop improvement.</p>
            </div>

            <!-- First Row - 4 Members -->
            <div class="team-row">
                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/kutub_500.png" alt="Dr. Kutubuddin Ali Molla">
                        <div class="team-social">
                            <a class="social-btn" href="https://orcid.org/0000-0002-9897-7906" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn" href="https://www.linkedin.com/in/kutubuddin-molla-12415619/"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Dr. Kutubuddin Ali Molla</h3>
                        <div class="team-role">Senior Scientist</div>
                        <div class="team-division">Crop Improvement Division</div>
                        <div class="team-institute">ICAR-CRRI, Cuttack</div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/asif_500.png" alt="Asif Ali Vadakkethil">
                        <div class="team-social">
                            <a class="social-btn" href="https://orcid.org/0009-0007-4711-9200" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn" href="https://www.linkedin.com/in/asif-ali-v-k-18b614136/"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Asif Ali Vadakkethil</h3>
                        <div class="team-role">PhD Research Scholar</div>
                        <div class="team-division">Agricultural Bioinformatics Division</div>
                        <div class="team-institute">ICAR-IASRI, New Delhi</div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/sonali_500.png" alt="Sonali Panda">
                        <div class="team-social">
                            <a class="social-btn" href="https://orcid.org/0009-0002-2503-9545" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn" href="https://www.linkedin.com/in/sonali-panda-059aa9340/"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Sonali Panda</h3>
                        <div class="team-role">Research Associate</div>
                        <div class="team-division">Crop Improvement Division</div>
                        <div class="team-institute">ICAR-CRRI, Cuttack</div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/tanmoy_500.png" alt="Dr. Tanmoy Halder">
                        <div class="team-social">
                            <a class="social-btn" href="https://orcid.org/0000-0002-6640-0950" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn" href="https://www.linkedin.com/in/tanmoy-halder-phd-6544a66a/"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Dr. Tanmoy Halder</h3>
                        <div class="team-role">Research Associate</div>
                        <div class="team-division">Crop Improvement Division</div>
                        <div class="team-institute">ICAR-CRRI, Cuttack</div>
                    </div>
                </div>
            </div>

            <div class="section-divider"></div>

            <!-- Second Row - 4 Members -->
            <div class="team-row">
                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/anusha_500.png" alt="Anusha T">
                        <div class="team-social">
                            <a class="social-btn" href="https://orcid.org/0009-0009-6912-8540" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn" href="https://www.linkedin.com/in/anusha-t-52a0b0252/"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Anusha T</h3>
                        <div class="team-role">PhD Research Scholar</div>
                        <div class="team-division">Crop Improvement Division</div>
                        <div class="team-institute">ICAR-CRRI, Cuttack</div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/muskan_500.png" alt="Muskan Parween">
                        <div class="team-social">
                            <a class="social-btn" href="https://orcid.org/0009-0002-7481-5889" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn" href="https://www.linkedin.com/in/muskan-parween-1aa9b224b"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Muskan Parween</h3>
                        <div class="team-role">Senior Research Fellow</div>
                        <div class="team-division">Crop Improvement Division</div>
                        <div class="team-institute">ICAR-CRRI, Cuttack</div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/mir_500.png" alt="Dr. Mir Asif Iquebal">
                        <div class="team-social">
                            <a class="social-btn" href="https://orcid.org/0000-0003-3787-5997" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn"
                                href="https://www.linkedin.com/in/mir-asif-iquebal-9b885812/?originalSubdomain=in"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Dr. Mir Asif Iquebal</h3>
                        <div class="team-role">Principal Scientist</div>
                        <div class="team-division">Agricultural Bioinformatics Division</div>
                        <div class="team-institute">ICAR-IASRI, New Delhi</div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/sarika_500.png" alt="Dr. Sarika">
                        <div class="team-social">
                            <a class="social-btn" href="https://orcid.org/0000-0002-9948-4994" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn"
                                href="https://www.linkedin.com/in/sarika-jaiswal-2291a4b/?originalSubdomain=in"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Dr. Sarika</h3>
                        <div class="team-role">Principal Scientist</div>
                        <div class="team-division">Agricultural Bioinformatics Division</div>
                        <div class="team-institute">ICAR-IASRI, New Delhi</div>
                    </div>
                </div>
            </div>

            <div class="section-divider"></div>

            <!-- Third Row - 2 Members -->
            <div class="team-row final-row">
                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/baig_500.png" alt="Dr. Mirza Jaynul Baig">
                        <div class="team-social">
                            <a class="social-btn"
                                href="https://icar-nrri.in/scientific-staff/#1528188740174-8c274aa9-3911"
                                target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn" href="#" target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Dr. Mirza Jaynul Baig</h3>
                        <div class="team-role">Former Director <br>(In-charge)</div>
                        <div class="team-division"></div>
                        <div class="team-institute">ICAR-CRRI, Cuttack</div>
                    </div>
                </div>

                <div class="team-card">
                    <div class="team-image-container">
                        <img class="team-image" src="img/team/angadi_500.png" alt="Dr. Ulavappa B. Angadi">
                        <div class="team-social">
                            <a class="social-btn" href="https://iasri-old.icar.gov.in/ub-angadi/" target="_blank">
                                <i class="fas fa-address-card"></i>
                            </a>
                            <a class="social-btn" href="https://www.linkedin.com/in/ulavappa-angadi-7b416736/"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Dr. Ulavappa B. Angadi</h3>
                        <div class="team-role">Principal Scientist</div>
                        <div class="team-division">Agricultural Bioinformatics Division</div>
                        <div class="team-institute">ICAR-IASRI, New Delhi</div>
                    </div>
                </div>
            </div>

            <div class="section-divider"></div>
        </div>
    </div>
</section>
<?php include_once 'footer.php'; ?>