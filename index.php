<?php
$ipAddress = $_SERVER['REMOTE_ADDR'];
$ipVoteFileName = "ipVote.json";
$votesFileName = "votes.json";

// Check if the IP address is already present in the file
$ipExists = false;
$fileContent_ipVote = file_get_contents($ipVoteFileName);
if ($fileContent_ipVote !== false) {
    $ipData = json_decode($fileContent_ipVote, true);

    if (isset($ipData[$ipAddress])) {
        $lastVoteTime = strtotime($ipData[$ipAddress]);
        $currentTime = time();
        $timeDifference = $currentTime - $lastVoteTime;

        // If less than 1 minute has passed, restrict the vote
        if ($timeDifference < 60) {
            $ipExists = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If IP address does not exist or 1 minute has passed, process the vote
    if (!$ipExists) {
        $selectedLanguage = $_POST['language'];
        $votes = [];

        // Read the votes from the file, if it exists
        if (file_exists($votesFileName)) {
            $votes = json_decode(file_get_contents($votesFileName), true);
        }

        // Increment the vote count for the selected language
        if (isset($votes[$selectedLanguage])) {
            $votes[$selectedLanguage]++;
        } else {
            $votes[$selectedLanguage] = 1;
            $defaultLanguages = ['C++', 'C#', 'JavaScript', 'PHP', 'Java'];
            // Add other languages to the votes array with 0 count
            foreach ($defaultLanguages as $language) {
                if (!isset($votes[$language])) {
                    $votes[$language] = 0;
                }
            }
        }

        // Calculate the percentages
        $totalVotes = array_sum($votes);
        $percentages = [];
        foreach ($votes as $language => $count) {
            $percentage = ($count / $totalVotes) * 100;
            $percentages[$language] = round($percentage, 2);
        }

        // Update the votes file with the new data
        file_put_contents($votesFileName, json_encode($votes));

        // Update the IP vote file with the current IP and time
        $ipData[$ipAddress] = date('Y-m-d H:i:s');
        file_put_contents($ipVoteFileName, json_encode($ipData));
    } else {
        // IP address already exists, so voting is restricted
        echo "<p>You have already voted. Please wait for 1 minute before voting again.</p>";
    }
}

// Read the current votes from the file, if it exists
$votes = [];
if (file_exists($votesFileName)) {
    $votes = json_decode(file_get_contents($votesFileName), true);
    if ($votes === null) {
        $votes = []; // Initialize as empty array if decoding fails
    }
}

// Calculate the percentages
$totalVotes = array_sum($votes);
$percentages = [];
foreach ($votes as $language => $count) {
    $percentage = ($count / $totalVotes) * 100;
    $percentages[$language] = round($percentage, 2);
}

// Set default values if no votes are recorded
if (empty($votes)) {
    $defaultLanguages = ['C++', 'C#', 'JavaScript', 'PHP', 'Java'];
    foreach ($defaultLanguages as $language) {
        $votes[$language] = 0;
        $percentages[$language] = 0.00;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Internet Voting</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
        }

        h1 {
            margin-top: 50px;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .radio-group {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Align items to the left */
        }

        .radio-group input[type="radio"] {
            margin-right: 10px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
        }

        table {
            margin-top: 50px;
            border-collapse: collapse;
            width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        #vote-button {
            margin-top: 30px;
            padding: 10px 25px;
            font-size: 14px;
        }

        /* CSS styles for the voting result rectangles */
        .result-bar {
            width: 100%;
            height: 20px;
            background-color: gray;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<h1>Internet Voting</h1>
<div class="container">
    <h2>Which programming language would you prefer?</h2>
    <form method="post">
        <div class="radio-group">
            <?php foreach ($votes as $language => $count) { ?>
                <label>
                    <input type="radio" name="language" value="<?php echo $language; ?>">
                    <?php echo $language; ?>
                </label><br>
            <?php } ?>
        </div>
        <button type="submit" id="vote-button">Vote</button>
    </form>
</div>

<table>
    <tr>
        <th>Programming language</th>
        <th>% of votes</th>
        <th></th>
    </tr>
    <?php foreach ($votes as $language => $count) { ?>
        <tr>
            <td><?php echo $language; ?></td>
            <td>
                <div class="result-bar" style="width: <?php echo $percentages[$language]; ?>%;"></div>
            </td>
            <td><?php echo $percentages[$language]; ?>%</td>
        </tr>
    <?php } ?>
</table>
</body>
</html>
