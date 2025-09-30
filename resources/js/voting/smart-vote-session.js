class SmartVoteSession {
    constructor(authorization) {
        this.auth = authorization;
        this.timeLeft = authorization.time_left;
        this.selections = {};
        this.timerInterval = null;
        
        this.init();
    }

    init() {
        this.startTimer();
        this.trackActivity();
        this.setupAutoSave();
    }

    startTimer() {
        this.timerInterval = setInterval(() => {
            this.timeLeft--;
            this.updateTimerDisplay();
            this.checkWarnings();
            
            if (this.timeLeft <= 0) {
                this.handleExpiry();
            }
        }, 1000);
    }

    trackActivity() {
        ['click', 'change', 'keypress'].forEach(event => {
            document.addEventListener(event, () => {
                this.reportActivity('user_interaction');
            });
        });

        document.addEventListener('change', (e) => {
            if (e.target.name && e.target.name.startsWith('position_')) {
                this.handleSelectionChange(e.target);
            }
        });
    }

    async reportActivity(action, data = {}) {
        try {
            const response = await fetch('/api/vote/activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auth_id: this.auth.id,
                    action: action,
                    data: data
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.extended) {
                    this.timeLeft = result.time_left;
                    this.showExtensionNotice();
                }
            }
        } catch (error) {
            console.error('Activity tracking failed:', error);
        }
    }

    handleSelectionChange(element) {
        const positionId = element.name.replace('position_', '');
        this.selections[positionId] = element.value;
        
        this.reportActivity('selection_made', { selections: this.selections });
        this.saveToLocalStorage();
    }

    setupAutoSave() {
        setInterval(() => this.saveToLocalStorage(), 30000);
    }

    saveToLocalStorage() {
        localStorage.setItem(`vote_draft_${this.auth.election_id}`, JSON.stringify({
            selections: this.selections,
            saved_at: Date.now()
        }));
    }

    updateTimerDisplay() {
        const minutes = Math.floor(this.timeLeft / 60);
        const seconds = this.timeLeft % 60;
        const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        const timerElement = document.getElementById('vote-timer');
        if (timerElement) {
            timerElement.textContent = display;
            timerElement.className = this.getTimerClass();
        }
    }

    getTimerClass() {
        if (this.timeLeft > 300) return 'text-green-600';
        if (this.timeLeft > 120) return 'text-yellow-600';
        if (this.timeLeft > 30) return 'text-orange-600';
        return 'text-red-600 font-bold animate-pulse';
    }

    checkWarnings() {
        if (this.timeLeft === 300) {
            this.showWarning('5 minutes remaining');
        } else if (this.timeLeft === 120) {
            this.showWarning('2 minutes remaining');
        } else if (this.timeLeft === 30) {
            this.showCriticalWarning('30 seconds remaining!');
        }
    }

    async handleExpiry() {
        clearInterval(this.timerInterval);
        
        if (confirm('Session expired. Continue voting?')) {
            await this.recoverSession();
        } else {
            this.exitVoting();
        }
    }

    async recoverSession() {
        try {
            const response = await fetch('/api/vote/cast', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auth_id: this.auth.id,
                    selections: {}
                })
            });

            const result = await response.json();

            if (result.status === 'session_recovered') {
                this.auth = result.authorization;
                this.timeLeft = result.authorization.time_left;
                
                if (result.draft) {
                    this.selections = result.draft;
                    this.restoreSelections();
                }
                
                this.startTimer();
                alert('Session recovered successfully');
            }
        } catch (error) {
            alert('Recovery failed: ' + error.message);
        }
    }

    async submitVote() {
        try {
            const response = await fetch('/api/vote/cast', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    auth_id: this.auth.id,
                    selections: this.selections
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                alert(`Vote cast successfully! Receipt: ${result.receipt.receipt_hash.substring(0, 16)}...`);
                localStorage.removeItem(`vote_draft_${this.auth.election_id}`);
                this.exitVoting();
            } else {
                alert('Vote failed: ' + (result.reason || 'Unknown error'));
            }
        } catch (error) {
            alert('Network error: ' + error.message);
        }
    }

    restoreSelections() {
        Object.entries(this.selections).forEach(([positionId, candidateId]) => {
            const element = document.querySelector(`[name="position_${positionId}"][value="${candidateId}"]`);
            if (element) element.checked = true;
        });
    }

    showWarning(message) {
        console.log('WARNING:', message);
    }

    showCriticalWarning(message) {
        console.log('CRITICAL:', message);
    }

    showExtensionNotice() {
        console.log('Session extended');
    }

    exitVoting() {
        window.location.href = '/dashboard';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const authData = window.voteAuthorization;
    if (authData) {
        window.voteSession = new SmartVoteSession(authData);
    }
});