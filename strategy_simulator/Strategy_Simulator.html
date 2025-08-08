<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omer's Social Strategy Simulator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 960px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .section-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .status-label {
            font-weight: 600;
            color: #4a5568;
        }
        .status-value {
            color: #2d3748;
        }
        .status-true {
            color: #10b981; /* Green */
            font-weight: bold;
        }
        .status-false {
            color: #ef4444; /* Red */
            font-weight: bold;
        }
        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            text-align: center;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border: none;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }
        .btn-secondary {
            background-color: #cbd5e0;
            color: #4a5568;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #a0aec0;
            box-shadow: 0 4px 8px rgba(196, 206, 220, 0.3);
        }
        .log-area {
            height: 250px;
            overflow-y: auto;
            background-color: #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.875rem;
            color: #2d3748;
            line-height: 1.4;
            white-space: pre-wrap; /* Preserve whitespace and wrap text */
        }
        .log-area p {
            margin-bottom: 0.25rem;
        }
        .log-action {
            font-weight: bold;
            color: #2d3748;
        }
        .log-outcome {
            color: #4a5568;
        }
        .log-warning {
            color: #f97316; /* Orange */
            font-weight: bold;
        }
        .log-success {
            color: #10b981; /* Green */
            font-weight: bold;
        }
        .textarea-notes {
            width: 100%;
            min-height: 80px;
            padding: 0.75rem;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            resize: vertical;
        }
        .saved-notes-display { /* New class for the display area */
            background-color: #f0f4f8;
            border: 1px solid #d1d9e2;
            border-radius: 6px;
            padding: 0.75rem;
            font-size: 0.875rem;
            color: #4a5568;
            white-space: pre-wrap; /* Preserve line breaks */
            overflow-y: auto; /* Allow scrolling if content is long */
            max-height: 120px; /* Limit height */
        }
        .log-type-selector {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .log-type-selector button {
            flex: 1;
            padding: 0.5rem;
            border-radius: 6px;
            border: 1px solid #cbd5e0;
            background-color: #f0f4f8;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .log-type-selector button.selected {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-4">Omer's Social Strategy Simulator</h1>

        <div class="section-card">
            <h2 class="text-xl font-semibold mb-3 text-gray-700">Current Status</h2>
            <div id="status-display">
                <div class="status-item">
                    <span class="status-label">Miryam Trusts Omer (General):</span>
                    <span id="miryam-trusts-omer" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Miryam Believes Omer Snitched:</span>
                    <span id="miryam-believes-omer-snitched" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Joe's Role Known by Yaakov:</span>
                    <span id="joe-known-by-yaakov" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Miryam Has Her Own Phone:</span>
                    <span id="miryam-has-own-phone" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Message Sent to David:</span>
                    <span id="message-sent-to-david" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Message Sent to Joe:</span>
                    <span id="message-sent-to-joe" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Yaakov Talked to David:</span>
                    <span id="yaakov-talked-to-david" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">David Clarified to Miryam:</span>
                    <span id="david-clarified-to-miryam" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Lois's Snitching Known by Group:</span>
                    <span id="lois-snitching-known-by-group" class="status-value"></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Miryam's Trust Improving:</span>
                    <span id="miryam-trust-improving" class="status-value"></span>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="font-semibold text-gray-700 mb-2">Key Relationships:</h3>
                <ul class="list-disc list-inside text-sm text-gray-600">
                    <li>Omer: Situationship with Millie, Crush on Miryam. Lois, Miryam, and Millie (a bit) think Omer is flirting with Shir.</li>
                    <li>Miryam: Brother is Yaakov. Attends Theater group with Nova, Roxy, Lois, and Angelina.</li>
                    <li>Yaakov: Miryam's brother, Best friends with David & Ravi. Roxy's ex.</li>
                    <li>Millie: Best friend of Miryam and Lois. Chill with Roxy. Omer's situationship. Thinks Omer is flirting with Shir (a bit).</li>
                    <li>Lois: Joe's ex, Miryam & Millie's best friend. Chill with Roxy. Recently snitched on vaping (Porto). **It is widely known among the girls (Miryam, Roxy, Nova) that Lois snitched.** Attends Theater group with Nova, Miryam, Roxy, and Angelina. Thinks Omer is flirting with Shir.</li>
                    <li>Joe: Lois's ex, the actual snitch to David. Trusted by girls.</li>
                    <li>David: Knows Joe snitched, wants Omer to take blame. Best friends with Yaakov & Ravi.</li>
                    <li>Roxy: Yaakov's ex, good friend of Miryam. Chill with Millie and Lois. Attends Theater group with Nova, Miryam, Lois, and Angelina.</li>
                    <li>Tobi (Tobias): Miryam's situationship. Goes to a different school.</li>
                    <li>Ravi: Best friends with David & Yaakov.</li>
                    <li>Nova: Present during vaping incident in Porto. Attends Theater group with Miryam, Roxy, Lois, and Angelina.</li>
                    <li>Emma & Lia: Were present during vaping incident (Lia at sleepover, Emma at Porto vaping). Both attended Theater group, but **have left the school**.</li>
                    <li>Angelina: Theater group guide/teacher.</li>
                    <li>Shir: Girl whom Lois, Miryam, and Millie (a bit) think Omer is flirting with.</li>
                </ul>
            </div>
        </div>

        <div class="section-card">
            <h2 class="text-xl font-semibold mb-3 text-gray-700">Actions</h2>
            <div class="flex flex-col sm:flex-row gap-4">
                <button id="sendYaakovBtn" class="btn btn-primary flex-1">Send Long Text to Yaakov</button>
                <button id="simulateMiryamPhoneBtn" class="btn btn-secondary flex-1">Simulate Miryam Gets Her Own Phone</button>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 mt-4">
                <button id="messageDavidBtn" class="btn btn-secondary flex-1">Message David</button>
                <button id="messageJoeBtn" class="btn btn-secondary flex-1">Message Joe</button>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 mt-4">
                <button id="yaakovTalksToDavidBtn" class="btn btn-primary flex-1" disabled>Yaakov Talks to David</button>
                <button id="davidClarifiesToMiryamBtn" class="btn btn-secondary flex-1" disabled>David Clarifies to Miryam</button>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 mt-4">
                <button id="groupLearnsLoisSnitchingBtn" class="btn btn-secondary flex-1" disabled>Group Learns About Lois's Snitching</button>
                <button id="talkToRoxyBtn" class="btn btn-secondary flex-1">Talk to Roxy (Potential Influence)</button>
            </div>
            <div class="mt-4">
                 <button id="tryMessageMiryamBtn" class="btn btn-primary w-full" disabled>Try Messaging Miryam (WhatsApp)</button>
                 <p class="text-xs text-gray-500 mt-2 text-center">This button will be enabled once Miryam has her own phone.</p>
            </div>
            <div class="mt-4">
                <button id="clearLogBtn" class="btn btn-secondary w-full">Clear Log</button>
            </div>
        </div>

        <div class="section-card">
            <h2 class="text-xl font-semibold mb-3 text-gray-700">Custom Log Entry</h2>
            <textarea id="customLogInput" class="textarea-notes mb-2" placeholder="Add a custom event or message to the log here (e.g., 'Omer saw Miryam and Tobi together at the mall')."></textarea>
            <div class="log-type-selector">
                <button id="logTypeAction" class="selected">Action</button>
                <button id="logTypeOutcome">Outcome</button>
                <button id="logTypeWarning">Warning</button>
                <button id="logTypeSuccess">Success</button>
                <button id="logTypeInfo">Info</button>
            </div>
            <button id="addCustomLogBtn" class="btn btn-secondary w-full mt-2">Add to Log</button>
        </div>

        <div class="section-card">
            <h2 class="text-xl font-semibold mb-3 text-gray-700">Console Log</h2>
            <div id="console-log" class="log-area">
                </div>
        </div>
    </div>

    <script>
        // Define a maximum number of log entries to prevent lag
        const MAX_LOG_ENTRIES = 100; // Keep the log to a manageable size

        // Game State Object
        const gameState = {
            miryamTrustsOmer: false, 
            miryamBelievesOmerSnitched: true, 
            joeIsRealSnitchKnownByYaakov: false, 
            miryamHasOwnPhone: false,
            messageSentToDavid: false, 
            messageSentToJoe: false,   
            yaakovTalkedToDavid: false, 
            davidClarifiedToMiryam: false, 
            loisSnitchingKnownByGroup: true, // Set to true by default
            miryamTrustImproving: false, 
            consoleLog: [] 
        };

        // --- UI Elements --- 
        // Declared as global variables so they are accessible by all functions
        let miryamTrustsOmerEl, miryamBelievesOmerSnitchedEl, joeKnownByYaakovEl, miryamHasOwnPhoneEl;
        let messageSentToDavidEl, messageSentToJoeEl, yaakovTalkedToDavidEl, davidClarifiedToMiryamEl;
        let loisSnitchingKnownByGroupEl, miryamTrustImprovingEl;
        let consoleLogEl;
        let sendYaakovBtn, simulateMiryamPhoneBtn, tryMessageMiryamBtn, clearLogBtnElement;
        let customLogInput, addCustomLogBtn, logTypeActionBtn, logTypeOutcomeBtn, logTypeWarningBtn, logTypeSuccessBtn, logTypeInfoBtn;
        let messageDavidBtn, messageJoeBtn;
        let talkToRoxyBtn, yaakovTalksToDavidBtn, davidClarifiesToMiryamBtn, groupLearnsLoisSnitchingBtn;

        let selectedLogType = 'action'; // Default log type

        // --- Functions ---
        /**
         * Logs a message to the console area in the UI.
         * @param {string} type - 'action', 'outcome', 'warning', 'success', 'info'
         * @param {string} message - The message content.
         */
        function logMessage(type, message) {
            const p = document.createElement('p');
            p.classList.add(`log-${type}`);
            p.textContent = message;
            consoleLogEl.appendChild(p);

            // Enforce log limit
            if (consoleLogEl.children.length > MAX_LOG_ENTRIES) {
                consoleLogEl.removeChild(consoleLogEl.firstChild);
            }

            consoleLogEl.scrollTop = consoleLogEl.scrollHeight; 
            gameState.consoleLog.push({ type, message }); 
        }

        /**
         * Resets the game state to its initial values and clears the log.
         */
        function clearLog() {
            gameState.miryamTrustsOmer = false;
            gameState.miryamBelievesOmerSnitched = true;
            gameState.joeIsRealSnitchKnownByYaakov = false;
            gameState.miryamHasOwnPhone = false;
            gameState.messageSentToDavid = false;
            gameState.messageSentToJoe = false;
            gameState.yaakovTalkedToDavid = false; 
            gameState.davidClarifiedToMiryam = false; 
            gameState.loisSnitchingKnownByGroup = true; // Set to true by default
            gameState.miryamTrustImproving = false; 
            gameState.consoleLog = [];

            // Re-enable all action buttons as appropriate for reset state
            sendYaakovBtn.disabled = false;
            simulateMiryamPhoneBtn.disabled = false;
            tryMessageMiryamBtn.disabled = true;
            messageDavidBtn.disabled = false; 
            messageJoeBtn.disabled = false;   
            yaakovTalksToDavidBtn.disabled = true; 
            davidClarifiesToMiryamBtn.disabled = true; 
            groupLearnsLoisSnitchingBtn.disabled = true; // Disabled because it's known by default
            talkToRoxyBtn.disabled = false; 


            // Clear console log display
            consoleLogEl.innerHTML = '';
            // Clear custom input
            customLogInput.value = "";
            selectLogType('action'); // Reset to default selected log type
            updateUI(); // Ensure UI is updated after reset
            logMessage('info', "Simulation Reset. Start fresh!");
        }

        /**
         * Updates the UI to reflect the current game state.
         */
        function updateUI() {
            miryamTrustsOmerEl.textContent = gameState.miryamTrustsOmer ? 'Yes (Rebuilding)' : 'No (Distrusting)';
            miryamTrustsOmerEl.className = gameState.miryamTrustsOmer ? 'status-true' : 'status-false';

            miryamBelievesOmerSnitchedEl.textContent = gameState.miryamBelievesOmerSnitched ? 'Yes' : 'No (Understanding)';
            miryamBelievesOmerSnitchedEl.className = gameState.miryamBelievesOmerSnitched ? 'status-false' : 'status-true';

            joeKnownByYaakovEl.textContent = gameState.joeIsRealSnitchKnownByYaakov ? 'Yes (Yaakov informed)' : 'No (Yaakov unaware)';
            joeKnownByYaakovEl.className = gameState.joeIsRealSnitchKnownByYaakov ? 'status-true' : 'status-false';

            miryamHasOwnPhoneEl.textContent = gameState.miryamHasOwnPhone ? 'Yes' : 'No (Using friends\' phones)';
            miryamHasOwnPhoneEl.className = gameState.miryamHasOwnPhone ? 'status-true' : 'status-false';

            messageSentToDavidEl.textContent = gameState.messageSentToDavid ? 'Yes' : 'No';
            messageSentToDavidEl.className = gameState.messageSentToDavid ? 'status-true' : 'status-false';

            messageSentToJoeEl.textContent = gameState.messageSentToJoe ? 'Yes' : 'No';
            messageSentToJoeEl.className = gameState.messageSentToJoe ? 'status-true' : 'status-false';

            yaakovTalkedToDavidEl.textContent = gameState.yaakovTalkedToDavid ? 'Yes' : 'No';
            yaakovTalkedToDavidEl.className = gameState.yaakovTalkedToDavid ? 'status-true' : 'status-false';

            davidClarifiedToMiryamEl.textContent = gameState.davidClarifiedToMiryam ? 'Yes' : 'No';
            davidClarifiedToMiryamEl.className = gameState.davidClarifiedToMiryam ? 'status-true' : 'status-false';

            loisSnitchingKnownByGroupEl.textContent = gameState.loisSnitchingKnownByGroup ? 'Yes' : 'No';
            loisSnitchingKnownByGroupEl.className = gameState.loisSnitchingKnownByGroup ? 'status-true' : 'status-false';

            miryamTrustImprovingEl.textContent = gameState.miryamTrustImproving ? 'Yes' : 'No';
            miryamTrustImprovingEl.className = gameState.miryamTrustImproving ? 'status-true' : 'status-false';

            // Update button disabled states based on game logic
            tryMessageMiryamBtn.disabled = !gameState.miryamHasOwnPhone;
            messageDavidBtn.disabled = gameState.messageSentToDavid;
            messageJoeBtn.disabled = gameState.messageSentToJoe;
            
            yaakovTalksToDavidBtn.disabled = !gameState.joeIsRealSnitchKnownByYaakov || gameState.yaakovTalkedToDavid; 
            
            davidClarifiesToMiryamBtn.disabled = !gameState.yaakovTalkedToDavid || gameState.davidClarifiedToMiryam;
            
            groupLearnsLoisSnitchingBtn.disabled = gameState.loisSnitchingKnownByGroup; // Disabled if already known
            // talkToRoxyBtn.disabled = gameState.talkedToRoxy; // If we add a state for it
        }

        /**
         * Simulates sending the long, detailed text message to Yaakov.
         */
        function sendYaakovMessage() {
            if (sendYaakovBtn.disabled) {
                logMessage('warning', "You've already sent the message to Yaakov. Clear the log to start a new simulation.");
                return;
            }

            logMessage('action', "ACTION: Sending long, detailed text to Yaakov...");
            const message = `Yo Yaakov, I really need your help with something important. Miryam is telling people I'm not trustworthy, and she specifically thinks I snitched about us smoking at the sleepover. She genuinely believes I betrayed her trust there, and that's why she's saying I tell everything to you, David, and Ravi.

But the truth is, Joe was the one who told David about it. I swear I didn't say anything, and you know I don't just 'tell everything.'

I also heard about the theater group in Porto and how Lois snitched about the vaping. It's crazy how I'm getting blamed for snitching when Lois just did something similar.

Dude, I'm stuck. As her brother, and being best friends with David, you're the only one who can really help me clear my name. Can you please talk to David and get him to clarify that Joe was the one who told him? And more importantly, can you talk to Miryam when you can? She needs to know the truth, that I didn't betray her trust like she thinks. It's really messing things up for me.`;
            logMessage('outcome', `Message sent to Yaakov: "${message}"`);
            logMessage('info', "Yaakov will read this privately. Expect a delay as he processes it and decides how to act. This enables Yaakov to talk to David.");

            gameState.joeIsRealSnitchKnownByYaakov = true; 
            sendYaakovBtn.disabled = true; 
            updateUI();
        }

        /**
         * Simulates Miryam getting her own phone back.
         */
        function simulateMiryamGetsPhone() {
            if (gameState.miryamHasOwnPhone) {
                logMessage('warning', "Miryam already has her own phone. Clear the log to start a new simulation.");
                return;
            }

            logMessage('action', "ACTION: Simulating Miryam gets her own phone back...");
            gameState.miryamHasOwnPhone = true;
            logMessage('success', "SUCCESS: Miryam now has her own phone! You can try messaging her directly.");
            simulateMiryamPhoneBtn.disabled = true; 
            updateUI();
        }

        /**
         * Attempts to send the short message to Miryam. Checks for phone availability.
         */
        function tryMessageMiryam() {
            if (tryMessageMiryamBtn.disabled && gameState.miryamHasOwnPhone) { 
                 logMessage('warning', "You've already sent the message to Miryam. Clear the log to start a new new simulation.");
                 return;
            }

            if (!gameState.miryamHasOwnPhone) {
                logMessage('warning', "WARNING: Miryam does NOT have her own phone yet. Messaging her now via friends' phones is VERY RISKY (Millie, Lois, Roxy will likely see it and cause drama!). Action aborted.");
                return;
            }

            logMessage('action', "ACTION: Sending short message to Miryam on WhatsApp (private)...");
            const message = `Hey Miryam, I miss our friendship, especially seeing you in class every day (kinda hard not to, being the only guy lol). I heard you think I snitched about the sleepover, and that you don't trust me because you think I tell David, Yaakov, and Ravi everything. I swear I didn't snitch, and I don't just tell them everything. Joe actually told David. I feel really bad about the mix-up and that you're upset. I still think you're cool, like I said about my small crush, and I just want us to be friends again, no drama. I'm cool with Tobi. Can we talk to clear this up?`;
            logMessage('outcome', `Message sent to Miryam: "${message}"`);
            logMessage('success', "SUCCESS: Message sent privately to Miryam. She now has the direct facts from you.");

            gameState.miryamBelievesOmerSnitched = false; 
            if (gameState.joeIsRealSnitchKnownByYaakov || gameState.davidClarifiedToMiryam) {
                gameState.miryamTrustsOmer = true; 
                gameState.miryamTrustImproving = true; 
                logMessage('outcome', "Miryam's trust in you is starting to rebuild!");
            } else {
                logMessage('outcome', "Miryam has your message. Trust rebuilding will still depend on Yaakov's/David's follow-up.");
            }
            tryMessageMiryamBtn.disabled = true; 
            updateUI();
        }

        /**
         * Simulates sending a message to David.
         */
        function sendMessageToDavid() {
            if (gameState.messageSentToDavid) {
                logMessage('warning', "You've already messaged David. Clear the log to start a new simulation.");
                return;
            }

            logMessage('action', "ACTION: Messaging David directly...");
            const message = `Hey David, I know you're tight with Joe, but Miryam thinks I snitched about the sleepover and it's really messing up our friendship. You know Joe told you, right? I didn't say anything. Can you please help me out and tell Miryam it wasn't me? I won't make a big deal out of it.`;
            logMessage('outcome', `Message sent to David: "${message}"`);
            logMessage('info', "David has received your message. He might be conflicted due to loyalty to Joe. This could potentially help clear your name or worsen David's stance, depending on his personality. It's generally better for Yaakov to talk to him.");

            gameState.messageSentToDavid = true;
            updateUI();
        }

        /**
         * Simulates sending a message to Joe.
         */
        function sendMessageToJoe() {
            if (gameState.messageSentToJoe) {
                logMessage('warning', "You've already messaged Joe. Clear the log to start a new simulation.");
                return;
            }

            logMessage('action', "ACTION: Messaging Joe directly...");
            const message = `Hey Joe, just wanted to clear something up. Miryam thinks I snitched about the sleepover, but I heard you told David. It's causing me a lot of problems right now. I didn't say anything to anyone.`;
            logMessage('outcome', `Message sent to Joe: "${message}"`);
            logMessage('warning', "WARNING: Direct messaging Joe is risky. He might deny or get defensive, potentially causing more drama. This action is generally NOT recommended in your situation.");

            gameState.messageSentToJoe = true;
            updateUI();
        }

        /**
         * Simulates Yaakov talking to David about Joe.
         */
        function yaakovTalksToDavid() {
            if (yaakovTalksToDavidBtn.disabled) {
                logMessage('warning', "Yaakov needs to know the full story (Send Long Text to Yaakov) before talking to David, or he already did.");
                return;
            }
            logMessage('action', "ACTION: Yaakov talks to David about Joe's role in the snitching.");
            logMessage('outcome', "Yaakov has influential conversations with David. David is now aware that Yaakov knows the truth. This enables David to clarify to Miryam.");
            
            gameState.yaakovTalkedToDavid = true;
            updateUI();
        }

        /**
         * Simulates David clarifying the situation to Miryam (likely influenced by Yaakov).
         */
        function davidClarifiesToMiryam() {
            if (davidClarifiesToMiryamBtn.disabled) {
                logMessage('warning', "David needs to be influenced by Yaakov first (Yaakov Talks to David) or he already clarified.");
                return;
            }
            logMessage('action', "ACTION: David clarifies to Miryam that Omer didn't snitch, likely due to Yaakov's influence.");
            logMessage('success', "SUCCESS: Miryam hears from David that it wasn't Omer who snitched about the sleepover.");
            
            gameState.davidClarifiedToMiryam = true;
            gameState.miryamBelievesOmerSnitched = false; 
            gameState.miryamTrustsOmer = true; 
            gameState.miryamTrustImproving = true; 
            davidClarifiesToMiryamBtn.disabled = true; 
            updateUI();
        }

        /**
         * Simulates the group learning about Lois's snitching regarding the vaping incident.
         */
        function groupLearnsLoisSnitching() {
            if (groupLearnsLoisSnitchingBtn.disabled) {
                logMessage('warning', "The group already knows about Lois's snitching. Clear the log to reset.");
                return;
            }
            logMessage('action', "ACTION: The group (Miryam, Roxy, Nova) learns more widely about Lois's snitching regarding the vaping incident in Porto.");
            logMessage('info', "This could highlight the hypocrisy of Miryam's accusations against Omer, but it's a slow process.");
            
            gameState.loisSnitchingKnownByGroup = true;
            if (!gameState.miryamBelievesOmerSnitched && !gameState.miryamTrustsOmer) { 
                 logMessage('outcome', "This information is now circulating. It may subtly influence Miryam's perception of trustworthiness, but won't change everything instantly.");
                 gameState.miryamTrustImproving = true; 
            } else if (gameState.miryamBelievesOmerSnitched) {
                 logMessage('outcome', "This information is now circulating. It might make Miryam reflect on Lois, but doesn't immediately clear Omer's name directly related to the sleepover.");
            }
            groupLearnsLoisSnitchingBtn.disabled = true; 
            updateUI();
        }

        /**
         * Simulates talking to Roxy (potential influence).
         */
        function talkToRoxy() {
            if (talkToRoxyBtn.disabled) {
                logMessage('warning', "You've already talked to Roxy. Clear the log to reset.");
                return;
            }
            logMessage('action', "ACTION: Talking to Roxy (Yaakov's ex, Miryam's friend).");
            const message = `Hey Roxy, I'm trying to clear up the sleepover drama with Miryam. It wasn't me who snitched, Joe told David. It's frustrating because Miryam thinks I 'tell everything.' I heard about Lois and the vaping too, kinda ironic right?`;
            logMessage('outcome', `Message to Roxy: "${message}"`);
            logMessage('info', "Roxy has received your message. She is a good friend of Miryam and knows the social dynamics. Her influence is generally positive, but subtle. This might help reinforce the truth about Lois's snitching and indirectly influence Miryam's perception.");
            
            gameState.talkedToRoxy = true; // Added this state to disable button after use
            if (!gameState.loisSnitchingKnownByGroup) { 
                gameState.loisSnitchingKnownByGroup = true;
                logMessage('outcome', "Roxy might help spread awareness about Lois's snitching now.");
            }
            if (!gameState.miryamTrustImproving) { 
                gameState.miryamTrustImproving = true;
                logMessage('outcome', "Roxy's understanding might subtly contribute to Miryam's trust improving over time.");
            }

            talkToRoxyBtn.disabled = true; 
            updateUI();
        }

        /**
         * Handles adding custom input from the textarea to the log.
         */
        function addCustomLogEntry() {
            const message = customLogInput.value.trim();
            if (message) {
                logMessage(selectedLogType, message);
                customLogInput.value = ''; // Clear the input after adding
            } else {
                logMessage('warning', "Please enter some text for your custom log entry.");
            }
        }

        /**
         * Sets the active log type for custom entries.
         */
        function selectLogType(type) {
            selectedLogType = type;
            // Remove 'selected' class from all buttons
            logTypeActionBtn.classList.remove('selected');
            logTypeOutcomeBtn.classList.remove('selected');
            logTypeWarningBtn.classList.remove('selected');
            logTypeSuccessBtn.classList.remove('selected');
            logTypeInfoBtn.classList.remove('selected');

            // Add 'selected' class to the clicked button
            document.getElementById(`logType${type.charAt(0).toUpperCase() + type.slice(1)}`).classList.add('selected');
        }


        // This section will run once the HTML is parsed, ensuring elements are available.
        document.addEventListener('DOMContentLoaded', function() {
            // Assign UI elements
            miryamTrustsOmerEl = document.getElementById('miryam-trusts-omer');
            miryamBelievesOmerSnitchedEl = document.getElementById('miryam-believes-omer-snitched');
            joeKnownByYaakovEl = document.getElementById('joe-known-by-yaakov'); 
            miryamHasOwnPhoneEl = document.getElementById('miryam-has-own-phone');
            consoleLogEl = document.getElementById('console-log');
            sendYaakovBtn = document.getElementById('sendYaakovBtn');
            simulateMiryamPhoneBtn = document.getElementById('simulateMiryamPhoneBtn');
            tryMessageMiryamBtn = document.getElementById('tryMessageMiryamBtn');
            clearLogBtnElement = document.getElementById('clearLogBtn');
            
            customLogInput = document.getElementById('customLogInput'); 
            addCustomLogBtn = document.getElementById('addCustomLogBtn'); 
            logTypeActionBtn = document.getElementById('logTypeAction');
            logTypeOutcomeBtn = document.getElementById('logTypeOutcome');
            logTypeWarningBtn = document.getElementById('logTypeWarning');
            logTypeSuccessBtn = document.getElementById('logTypeSuccess');
            logTypeInfoBtn = document.getElementById('logTypeInfo');

            messageDavidBtn = document.getElementById('messageDavidBtn');
            messageJoeBtn = document.getElementById('messageJoeBtn');

            messageSentToDavidEl = document.getElementById('message-sent-to-david');
            messageSentToJoeEl = document.getElementById('message-sent-to-joe');
            yaakovTalkedToDavidEl = document.getElementById('yaakov-talked-to-david'); 
            davidClarifiedToMiryamEl = document.getElementById('david-clarified-to-miryam'); 
            loisSnitchingKnownByGroupEl = document.getElementById('lois-snitching-known-by-group'); 
            miryamTrustImprovingEl = document.getElementById('miryam-trust-improving'); 

            yaakovTalksToDavidBtn = document.getElementById('yaakovTalksToDavidBtn'); 
            davidClarifiesToMiryamBtn = document.getElementById('davidClarifiesToMiryamBtn'); 
            groupLearnsLoisSnitchingBtn = document.getElementById('groupLearnsLoisSnitchingBtn'); 
            talkToRoxyBtn = document.getElementById('talkToRoxyBtn'); 

            // --- Event Listeners ---
            sendYaakovBtn.addEventListener('click', sendYaakovMessage);
            simulateMiryamPhoneBtn.addEventListener('click', simulateMiryamGetsPhone);
            tryMessageMiryamBtn.addEventListener('click', tryMessageMiryam);
            clearLogBtnElement.addEventListener('click', clearLog);
            
            addCustomLogBtn.addEventListener('click', addCustomLogEntry);
            logTypeActionBtn.addEventListener('click', () => selectLogType('action'));
            logTypeOutcomeBtn.addEventListener('click', () => selectLogType('outcome'));
            logTypeWarningBtn.addEventListener('click', () => selectLogType('warning'));
            logTypeSuccessBtn.addEventListener('click', () => selectLogType('success'));
            logTypeInfoBtn.addEventListener('click', () => selectLogType('info'));

            messageDavidBtn.addEventListener('click', sendMessageToDavid);
            messageJoeBtn.addEventListener('click', sendMessageToJoe);

            yaakovTalksToDavidBtn.addEventListener('click', yaakovTalksToDavid);
            davidClarifiesToMiryamBtn.addEventListener('click', davidClarifiesToMiryam);
            groupLearnsLoisSnitchingBtn.addEventListener('click', groupLearnsLoisSnitching);
            talkToRoxyBtn.addEventListener('click', talkToRoxy);

            // Initial UI Update
            clearLog(); // Call clearLog to initialize the state and log
        });
    </script>
</body>
</html>
