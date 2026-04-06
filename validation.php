<?php
declare(strict_types=1);

/**
 * validation.php
 * Interface de validation / mise à jour de data_mcq_tmp
 */

mb_internal_encoding('UTF-8');
date_default_timezone_set('Africa/Douala');

/* =========================
   CONFIGURATION BDD
========================= */
$dbHost = ($_SERVER['HTTP_HOST']=="api.institutblaina.cm") ? 'localhost' : 'db5020009634.hosting-data.io';
$dbName = ($_SERVER['HTTP_HOST']=="api.institutblaina.cm") ? 'bymquiz' : 'dbs15434173';
$dbUser = ($_SERVER['HTTP_HOST']=="api.institutblaina.cm") ? 'root': 'dbu4573278';
$dbPass = ($_SERVER['HTTP_HOST']=="api.institutblaina.cm") ? '' : 'BymQu!z2016'; // à modifier si besoin

/* =========================
   CONNEXION PDO
========================= */
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Throwable $e) {
    http_response_code(500);
    die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
}

/* =========================
   FONCTIONS
========================= */
function cleanInput(?string $value): string
{
    return trim((string)$value);
}

function getRandomPendingMcq(PDO $pdo): ?array
{
    $sql = "
        SELECT *
        FROM data_mcq_tmp
        WHERE metadata IS NULL
        ORDER BY RAND()
        LIMIT 1
    ";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch();

    return $row ?: null;
}

function getMcqById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM data_mcq_tmp WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/* =========================
   TRAITEMENT FORMULAIRE
========================= */
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($id <= 0) {
        $message = "Identifiant invalide.";
        $messageType = 'error';
    } else {
        try {
            if ($action === 'ok') {
                $stmt = $pdo->prepare("
                    UPDATE data_mcq_tmp
                    SET metadata = 'OK'
                    WHERE id = :id
                ");
                $stmt->execute([':id' => $id]);

                $message = "La ligne a été marquée comme OK.";
                $messageType = 'success';
            } elseif ($action === 'pas_bon') {
                $stmt = $pdo->prepare("
                    UPDATE data_mcq_tmp
                    SET metadata = 'PAS_BON'
                    WHERE id = :id
                ");
                $stmt->execute([':id' => $id]);

                $message = "La ligne a été marquée comme PAS_BON.";
                $messageType = 'warning';
            } elseif ($action === 'mettre_a_jour') {
                $title = cleanInput($_POST['title'] ?? '');
                $question = cleanInput($_POST['question'] ?? '');
                $choiceA = cleanInput($_POST['choiceA'] ?? '');
                $choiceB = cleanInput($_POST['choiceB'] ?? '');
                $choiceC = cleanInput($_POST['choiceC'] ?? '');
                $choiceD = cleanInput($_POST['choiceD'] ?? '');
                $explication = cleanInput($_POST['explication'] ?? '');
                $bible_references = cleanInput($_POST['bible_references'] ?? '');
                $description = cleanInput($_POST['description'] ?? '');
                $keywords = cleanInput($_POST['keywords'] ?? '');

                if ($title === '' || $question === '') {
                    throw new RuntimeException("Les champs title et question sont obligatoires.");
                }

                $stmt = $pdo->prepare("
                    UPDATE data_mcq_tmp
                    SET
                        title = :title,
                        question = :question,
                        choiceA = :choiceA,
                        choiceB = :choiceB,
                        choiceC = :choiceC,
                        choiceD = :choiceD,
                        explication = :explication,
                        bible_references = :bible_references,
                        description = :description,
                        keywords = :keywords,
                        metadata = 'A_JOUR'
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':title' => $title,
                    ':question' => $question,
                    ':choiceA' => $choiceA,
                    ':choiceB' => $choiceB,
                    ':choiceC' => $choiceC,
                    ':choiceD' => $choiceD,
                    ':explication' => $explication,
                    ':bible_references' => $bible_references,
                    ':description' => $description,
                    ':keywords' => $keywords,
                    ':id' => $id,
                ]);

                $message = "La ligne a été mise à jour et marquée comme A_JOUR.";
                $messageType = 'success';
            } else {
                $message = "Action non reconnue.";
                $messageType = 'error';
            }
        } catch (Throwable $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
}

/* =========================
   CHARGEMENT D'UNE LIGNE
========================= */
$current = getRandomPendingMcq($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation QCM Bible</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root{
            --bg-1:#0f172a;
            --bg-2:#1e293b;
            --card:#ffffff;
            --text:#0f172a;
            --muted:#64748b;
            --primary:#2563eb;
            --primary-2:#1d4ed8;
            --success:#16a34a;
            --warning:#f59e0b;
            --danger:#dc2626;
            --border:#e2e8f0;
            --soft:#f8fafc;
            --good:#dcfce7;
            --good-border:#22c55e;
            --shadow:0 20px 40px rgba(15, 23, 42, .15);
            --radius:22px;
        }

        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            font-family:Inter, "Segoe UI", Roboto, Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.18), transparent 28%),
                radial-gradient(circle at top right, rgba(14,165,233,.14), transparent 22%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            min-height:100vh;
            color:#fff;
        }

        .wrapper{
            width:min(1100px, 94%);
            margin:30px auto;
        }

        .header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            margin-bottom:22px;
            flex-wrap:wrap;
        }

        .brand{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .logo{
            width:58px;
            height:58px;
            border-radius:18px;
            background:linear-gradient(135deg, #3b82f6, #06b6d4);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:26px;
            box-shadow:0 10px 25px rgba(59,130,246,.35);
        }

        .title-block h1{
            margin:0;
            font-size:clamp(24px, 3vw, 34px);
            line-height:1.1;
        }

        .title-block p{
            margin:6px 0 0;
            color:rgba(255,255,255,.8);
            font-size:14px;
        }

        .card{
            background:var(--card);
            color:var(--text);
            border-radius:var(--radius);
            box-shadow:var(--shadow);
            overflow:hidden;
        }

        .card-top{
            padding:22px 24px;
            border-bottom:1px solid var(--border);
            background:linear-gradient(180deg, #ffffff, #f8fbff);
        }

        .status-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
            gap:12px;
            margin-top:16px;
        }

        .status-box{
            background:var(--soft);
            border:1px solid var(--border);
            padding:14px 16px;
            border-radius:16px;
        }

        .status-box .label{
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.06em;
            color:var(--muted);
            margin-bottom:6px;
            font-weight:700;
        }

        .status-box .value{
            font-size:16px;
            font-weight:700;
            word-break:break-word;
        }

        .alert{
            margin:16px 24px 0;
            padding:14px 16px;
            border-radius:14px;
            font-weight:600;
        }

        .alert.success{
            background:#ecfdf5;
            color:#166534;
            border:1px solid #bbf7d0;
        }

        .alert.warning{
            background:#fffbeb;
            color:#92400e;
            border:1px solid #fde68a;
        }

        .alert.error{
            background:#fef2f2;
            color:#991b1b;
            border:1px solid #fecaca;
        }

        form{
            padding:24px;
        }

        .grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .full{
            grid-column:1 / -1;
        }

        .field{
            display:flex;
            flex-direction:column;
            gap:8px;
        }

        .field label{
            font-size:14px;
            font-weight:700;
            color:#0f172a;
        }

        .field input[type="text"],
        .field textarea{
            width:100%;
            border:1px solid var(--border);
            background:#fff;
            border-radius:16px;
            padding:14px 16px;
            font-size:15px;
            color:#0f172a;
            outline:none;
            transition:.2s ease;
            box-shadow:0 1px 2px rgba(15,23,42,.03);
        }

        .field input[type="text"]:focus,
        .field textarea:focus{
            border-color:#93c5fd;
            box-shadow:0 0 0 4px rgba(37,99,235,.12);
        }

        .field textarea{
            min-height:120px;
            resize:vertical;
        }

        .choice-card{
            border:2px solid var(--border);
            border-radius:18px;
            padding:14px;
            background:#fff;
            transition:.2s ease;
        }

        .choice-card.correct{
            background:var(--good);
            border-color:var(--good-border);
            box-shadow:0 10px 22px rgba(34,197,94,.12);
        }

        .choice-head{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            margin-bottom:10px;
        }

        .choice-badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:34px;
            height:34px;
            border-radius:50%;
            font-weight:800;
            color:#fff;
            background:#334155;
            flex-shrink:0;
        }

        .choice-card.correct .choice-badge{
            background:var(--success);
        }

        .choice-note{
            font-size:12px;
            font-weight:800;
            color:var(--success);
            text-transform:uppercase;
            letter-spacing:.05em;
        }

        .actions{
            display:flex;
            flex-wrap:wrap;
            gap:14px;
            margin-top:26px;
        }

        .btn{
            border:none;
            border-radius:16px;
            padding:14px 22px;
            font-size:15px;
            font-weight:800;
            cursor:pointer;
            transition:transform .15s ease, box-shadow .15s ease, opacity .15s ease;
            min-width:160px;
        }

        .btn:hover{
            transform:translateY(-1px);
        }

        .btn:active{
            transform:translateY(0);
        }

        .btn-primary{
            background:linear-gradient(135deg, var(--primary), var(--primary-2));
            color:#fff;
            box-shadow:0 10px 24px rgba(37,99,235,.26);
        }
		
		.btn-primarysecond{
            background:linear-gradient(135deg, red, orange);
            color:#fff;
            box-shadow:0 10px 24px rgba(37,99,235,.26);
        }

        .btn-success{
            background:linear-gradient(135deg, #16a34a, #15803d);
            color:#fff;
            box-shadow:0 10px 24px rgba(22,163,74,.24);
        }

        .btn-warning{
            background:linear-gradient(135deg, #f59e0b, #d97706);
            color:#fff;
            box-shadow:0 10px 24px rgba(245,158,11,.24);
        }

        .empty-state{
            padding:50px 24px;
            text-align:center;
        }

        .empty-state h2{
            margin:0 0 8px;
            font-size:28px;
            color:#0f172a;
        }

        .empty-state p{
            margin:0;
            color:var(--muted);
            font-size:16px;
        }

        .footer-note{
            margin-top:14px;
            color:rgba(255,255,255,.78);
            text-align:center;
            font-size:13px;
        }

        @media (max-width: 860px){
            .grid{
                grid-template-columns:1fr;
            }

            .actions{
                flex-direction:column;
            }

            .btn{
                width:100%;
            }

            .card-top,
            form{
                padding:18px;
            }

            .alert{
                margin-left:18px;
                margin-right:18px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="brand">
                <div class="logo">📖</div>
                <div class="title-block">
                    <h1>Validation des QCM bibliques</h1>
                    <p>Interface responsive pour réviser, corriger et classer les éléments de <strong>data_mcq_tmp</strong>.</p>
                </div>
            </div>
        </div>

		<div class="card">
			<?php if ($message !== ''): ?>
				<div class="alert <?php echo htmlspecialchars($messageType); ?>">
					<?php echo htmlspecialchars($message); ?>
				</div>
				<br/>
			<?php endif; ?>
			
		</div>
		<br/>

        <div class="card">
            <div class="card-top">
                <h2 style="margin:0; font-size:22px;">Validation / Mise à jour</h2>
                <div class="status-grid">
                    <div class="status-box">
                        <div class="label">Source</div>
                        <div class="value">bymquiz.data_mcq_tmp</div>
                    </div>
                    <div class="status-box">
                        <div class="label">Sélection</div>
                        <div class="value">1 ligne aléatoire où metadata est NULL</div>
                    </div>
                    <div class="status-box">
                        <div class="label">Actions</div>
                        <div class="value">OK / PAS_BON / A_JOUR</div>
                    </div>
                </div>
            </div>

            

            <?php if (!$current): ?>
                <div class="empty-state">
                    <h2>Aucune donnée à valider</h2>
                    <p>Toutes les lignes semblent déjà traitées, ou aucune ligne avec <strong>metadata = NULL</strong> n’est disponible.</p>
                </div>
            <?php else: ?>
                <?php
                    $correctChoice = strtoupper((string)$current['reponseChoice']);
                ?>
                <form method="post" action="">
                    <input type="hidden" name="id" value="<?php echo (int)$current['id']; ?>">

                    <div class="grid">
                        <div class="field full">
                            <label for="title">Titre</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars((string)$current['title']); ?>" required>
                        </div>

                        <div class="field full">
                            <label for="question">Question</label>
                            <textarea id="question" name="question" required><?php echo htmlspecialchars((string)$current['question']); ?></textarea>
                        </div>

                        <div class="field full">
                            <div class="choice-card <?php echo $correctChoice === 'A' ? 'correct' : ''; ?>">
                                <div class="choice-head">
                                    <span class="choice-badge">A</span>
                                    <?php if ($correctChoice === 'A'): ?>
                                        <span class="choice-note">Bonne réponse</span>
                                    <?php endif; ?>
                                </div>
                                <label for="choiceA">Choix A</label>
                                <textarea id="choiceA" name="choiceA"><?php echo htmlspecialchars((string)$current['choiceA']); ?></textarea>
                            </div>
                        </div>

                        <div class="field full">
                            <div class="choice-card <?php echo $correctChoice === 'B' ? 'correct' : ''; ?>">
                                <div class="choice-head">
                                    <span class="choice-badge">B</span>
                                    <?php if ($correctChoice === 'B'): ?>
                                        <span class="choice-note">Bonne réponse</span>
                                    <?php endif; ?>
                                </div>
                                <label for="choiceB">Choix B</label>
                                <textarea id="choiceB" name="choiceB"><?php echo htmlspecialchars((string)$current['choiceB']); ?></textarea>
                            </div>
                        </div>

                        <div class="field full">
                            <div class="choice-card <?php echo $correctChoice === 'C' ? 'correct' : ''; ?>">
                                <div class="choice-head">
                                    <span class="choice-badge">C</span>
                                    <?php if ($correctChoice === 'C'): ?>
                                        <span class="choice-note">Bonne réponse</span>
                                    <?php endif; ?>
                                </div>
                                <label for="choiceC">Choix C</label>
                                <textarea id="choiceC" name="choiceC"><?php echo htmlspecialchars((string)$current['choiceC']); ?></textarea>
                            </div>
                        </div>

                        <div class="field full">
                            <div class="choice-card <?php echo $correctChoice === 'D' ? 'correct' : ''; ?>">
                                <div class="choice-head">
                                    <span class="choice-badge">D</span>
                                    <?php if ($correctChoice === 'D'): ?>
                                        <span class="choice-note">Bonne réponse</span>
                                    <?php endif; ?>
                                </div>
                                <label for="choiceD">Choix D</label>
                                <textarea id="choiceD" name="choiceD"><?php echo htmlspecialchars((string)$current['choiceD']); ?></textarea>
                            </div>
                        </div>

                        <div class="field full">
                            <label for="explication">Explication</label>
                            <textarea id="explication" name="explication"><?php echo htmlspecialchars((string)$current['explication']); ?></textarea>
                        </div>

                        <div class="field">
                            <label for="bible_references">Références bibliques</label>
                            <textarea id="bible_references" name="bible_references"><?php echo htmlspecialchars((string)$current['bible_references']); ?></textarea>
                        </div>

                        <div class="field">
                            <label for="keywords">Mots-clés</label>
                            <textarea id="keywords" name="keywords"><?php echo htmlspecialchars((string)$current['keywords']); ?></textarea>
                        </div>

                        <div class="field full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo htmlspecialchars((string)$current['description']); ?></textarea>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" name="action" value="ok" class="btn btn-success">
                            OK
                        </button>

                        <button type="submit" name="action" value="pas_bon" class="btn btn-warning">
                            PAS BON
                        </button>

                        <button type="submit" name="action" value="mettre_a_jour" class="btn btn-primary">
                            METTRE A JOUR
                        </button>
						<a href="validation.php" class="btn btn-primarysecond">PASSER QUESTION SUIVANTE</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="footer-note">
            Interface de validation responsive — ordinateur, tablette et téléphone.
        </div>
    </div>
</body>
</html>