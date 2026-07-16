<?php include_once 'header.php'; ?>

<style>
    .contact-section {
        padding: 4rem 0;
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        min-height: calc(100vh - 70px);
    }

    .contact-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .contact-header {
        text-align: center;
        margin-bottom: 4rem;
        position: relative;
    }

    .contact-header::after {
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

    .contact-header h1 {
        font-size: 3.5rem;
        color: var(--text-primary);
        margin-bottom: 1rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .contact-header p {
        font-size: 1.3rem;
        color: var(--text-secondary);
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .contact-cards {
        display: flex;
        gap: 2rem;
        justify-content: center;
    }

    .contact-card {
        flex: 1;
        max-width: 420px;
        background: var(--bg-primary);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        box-shadow: var(--shadow-lg);
        border: 2px solid transparent;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .contact-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .contact-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
        border-color: var(--primary-color);
    }

    .contact-card .card-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), #0a5a9e);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        transition: transform 0.3s ease;
    }

    .contact-card:hover .card-icon {
        transform: scale(1.1);
    }

    .contact-card .card-icon i {
        font-size: 1.8rem;
        color: #fff;
    }

    .contact-card h3 {
        font-size: 1.4rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .contact-card .contact-name {
        font-size: 1.1rem;
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 0.3rem;
    }

    .contact-card .contact-designation {
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin-bottom: 0.3rem;
    }

    .contact-card .contact-institute {
        font-size: 0.9rem;
        color: var(--accent-color);
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .contact-card .contact-email a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        transition: color 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .contact-card .contact-email a:hover {
        color: #0a5a9e;
        text-decoration: underline;
    }

    @media (max-width: 992px) {
        .contact-cards {
            flex-wrap: wrap;
        }

        .contact-card {
            min-width: 280px;
        }

        .contact-header h1 {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 768px) {
        .contact-cards {
            flex-direction: column;
            align-items: center;
        }

        .contact-card {
            max-width: 100%;
            width: 100%;
        }

        .contact-header h1 {
            font-size: 2rem;
        }

        .contact-section {
            padding: 2rem 0;
        }
    }
</style>

<section class="contact-section">
    <div class="contact-container">
        <div class="contact-header">
            <h1>Contact Us</h1>
        </div>

        <div class="contact-cards">
            <!-- Contact 1 -->
            <div class="contact-card">
                <div class="card-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Contact 1</h3>
                <div class="contact-name">Asif Ali Vadakkethil</div>
                <div class="contact-designation">PhD Scholar</div>
                <div class="contact-institute">ICAR-IASRI, New Delhi</div>
                <div class="contact-email">
                    <a href="mailto:asifalivk7@gmail.com"><i class="fas fa-envelope"></i> asifalivk7@gmail.com</a>
                </div>
            </div>

            <!-- Contact 2 -->
            <div class="contact-card">
                <div class="card-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Contact 2</h3>
                <div class="contact-name">Dr. Kutubuddin Ali Molla</div>
                <div class="contact-designation">Senior Scientist</div>
                <div class="contact-institute">ICAR-NRRI, Cuttack</div>
                <div class="contact-email">
                    <a href="mailto:kutubjoy@gmail.com"><i class="fas fa-envelope"></i> kutubjoy@gmail.com</a>
                </div>
            </div>

            <!-- Contact 3 -->
            <div class="contact-card">
                <div class="card-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Contact 3</h3>
                <div class="contact-name">Dr. Mir Asif Iquebal</div>
                <div class="contact-designation">Principal Scientist</div>
                <div class="contact-institute">ICAR-IASRI, New Delhi</div>
                <div class="contact-email">
                    <a href="mailto:jiqubal@gmail.com"><i class="fas fa-envelope"></i> jiqubal@gmail.com</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>