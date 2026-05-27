
export interface SpamFilterResult {
  isSpam: boolean;
  confidence: number;
  reasons: string[];
}

export const checkForSpam = (
  email: string,
  message?: string,
  name?: string
): SpamFilterResult => {
  const reasons: string[] = [];
  let spamScore = 0;

  // Email checks
  if (email) {
    // Temporary/disposable email domains (expanded list)
    const disposableEmailDomains = [
      '10minutemail.com', 'tempmail.org', 'guerrillamail.com', 'mailinator.com',
      'throwaway.email', 'temp-mail.org', 'dispostable.com', 'yopmail.com',
      'maildrop.cc', 'sharklasers.com', 'getnada.com', 'mail.tm', 'inboxkitten.com',
      'tempail.com', 'emailondeck.com', 'mohmal.com', 'armyspy.com', 'cuvox.de',
      'dayrep.com', 'fleckens.hu', 'gustr.com', 'jourrapide.com', 'rhyta.com',
      'superrito.com', 'teleworm.us', 'einrot.com', 'fakeinbox.com'
    ];
    
    const emailDomain = email.split('@')[1]?.toLowerCase();
    if (disposableEmailDomains.includes(emailDomain)) {
      spamScore += 60;
      reasons.push('Disposable email domain detected');
    }

    // Common spam email patterns
    const spamPatterns = [
      /noreply|no-reply|donotreply/i,
      /admin@|test@|info@.*\.tk$|info@.*\.ml$/i,
      /\d{6,}@/,  // 6+ consecutive numbers before @
      /^[a-z]{1,3}\d{3,}@/i  // Short letters followed by many numbers
    ];

    spamPatterns.forEach(pattern => {
      if (pattern.test(email)) {
        spamScore += 25;
        reasons.push('Suspicious email pattern detected');
      }
    });

    // Suspicious email format checks
    if (/\+.*\+/.test(email) || email.includes('..') || email.includes('--')) {
      spamScore += 20;
      reasons.push('Suspicious email format');
    }

    // Too many numbers in email
    const numberCount = (email.match(/\d/g) || []).length;
    if (numberCount > 8) {
      spamScore += 15;
      reasons.push('Excessive numbers in email');
    }

    // Check for known spam TLDs
    const spamTlds = ['.tk', '.ml', '.ga', '.cf'];
    if (spamTlds.some(tld => emailDomain?.endsWith(tld))) {
      spamScore += 30;
      reasons.push('Suspicious domain extension');
    }
  }

  // Enhanced message content checks
  if (message) {
    const lowerMessage = message.toLowerCase();
    
    // Expanded spam keywords
    const spamKeywords = [
      'viagra', 'casino', 'lottery', 'winner', 'congratulations',
      'click here', 'act now', 'limited time', 'free money',
      'make money fast', 'work from home', 'no experience',
      'guaranteed', 'risk free', 'urgent', 'immediate',
      'bitcoin', 'cryptocurrency', 'investment opportunity',
      'get rich quick', 'lose weight fast', 'miracle cure',
      'million dollars', 'inheritance', 'nigerian prince',
      'tax refund', 'claim now', 'verify account', 'suspended account',
      'update payment', 'confirm identity', 'phishing', 'malware'
    ];

    const foundSpamWords = spamKeywords.filter(keyword => 
      lowerMessage.includes(keyword)
    );
    
    if (foundSpamWords.length > 0) {
      spamScore += foundSpamWords.length * 20;
      reasons.push(`Spam keywords detected: ${foundSpamWords.join(', ')}`);
    }

    // Check for suspicious URL patterns
    const suspiciousUrls = [
      /bit\.ly|tinyurl|t\.co|goo\.gl/gi,
      /http:\/\/[^\/]+\.tk|\.ml|\.ga|\.cf/gi,
      /\w+\.(exe|zip|rar|bat|scr|com|pif)/gi
    ];

    suspiciousUrls.forEach(pattern => {
      if (pattern.test(message)) {
        spamScore += 35;
        reasons.push('Suspicious URL or file extension detected');
      }
    });

    // Excessive links
    const linkCount = (message.match(/https?:\/\//gi) || []).length;
    if (linkCount > 2) {
      spamScore += 25;
      reasons.push('Excessive links detected');
    }

    // All caps (enhanced check)
    const capsPercentage = (message.match(/[A-Z]/g) || []).length / message.length;
    if (capsPercentage > 0.5 && message.length > 20) {
      spamScore += 20;
      reasons.push('Excessive capital letters');
    }

    // Repetitive characters or words
    if (/(.)\1{4,}/.test(message) || /\b(\w+)\s+\1\b/gi.test(message)) {
      spamScore += 15;
      reasons.push('Repetitive content detected');
    }

    // Check for common scam phrases
    const scamPhrases = [
      'send money', 'wire transfer', 'western union', 'moneygram',
      'advance fee', 'processing fee', 'transaction fee', 'transfer fee',
      'bank account', 'routing number', 'social security', 'ssn'
    ];

    const foundScamPhrases = scamPhrases.filter(phrase => 
      lowerMessage.includes(phrase)
    );

    if (foundScamPhrases.length > 0) {
      spamScore += foundScamPhrases.length * 30;
      reasons.push(`Scam phrases detected: ${foundScamPhrases.join(', ')}`);
    }

    // Message length check (too short or suspiciously long)
    if (message.length < 10) {
      spamScore += 10;
      reasons.push('Message too short');
    } else if (message.length > 2000) {
      spamScore += 15;
      reasons.push('Message unusually long');
    }
  }

  // Enhanced name checks
  if (name) {
    // Generic spam names (expanded)
    const spamNames = [
      'test', 'admin', 'user', 'guest', 'demo', 'sample',
      'example', 'temp', 'anonymous', 'unknown', 'null',
      'bot', 'spam', 'fake', 'phishing'
    ];
    
    if (spamNames.includes(name.toLowerCase())) {
      spamScore += 25;
      reasons.push('Generic or suspicious name detected');
    }

    // Check for numbers in name (more sophisticated)
    const numberInName = /\d/.test(name);
    const manyNumbers = (name.match(/\d/g) || []).length > 2;
    
    if (numberInName) {
      spamScore += manyNumbers ? 20 : 10;
      reasons.push(manyNumbers ? 'Excessive numbers in name' : 'Numbers in name');
    }

    // Check for suspicious name patterns
    const suspiciousNamePatterns = [
      /^[a-z]+\d+$/i,  // letters followed by numbers
      /^\w{1,2}$/,     // too short (1-2 characters)
      /^[^a-z\s-']/i   // starts with non-letter
    ];

    suspiciousNamePatterns.forEach(pattern => {
      if (pattern.test(name)) {
        spamScore += 15;
        reasons.push('Suspicious name pattern');
      }
    });
  }

  // Cross-field validation
  if (email && name && message) {
    // Check if name appears in email (suspicious for real users)
    const emailLocalPart = email.split('@')[0].toLowerCase();
    if (emailLocalPart.includes(name.toLowerCase()) && name.length > 3) {
      spamScore += 10;
      reasons.push('Name matches email pattern');
    }
  }

  return {
    isSpam: spamScore >= 50,
    confidence: Math.min(spamScore, 100),
    reasons
  };
};

// Rate limiting helper
const rateLimitMap = new Map<string, { count: number; lastReset: number }>();

export const checkRateLimit = (identifier: string, maxRequests: number = 5, windowMs: number = 300000): boolean => {
  const now = Date.now();
  const record = rateLimitMap.get(identifier);
  
  if (!record || now - record.lastReset > windowMs) {
    rateLimitMap.set(identifier, { count: 1, lastReset: now });
    return false; // Not rate limited
  }
  
  if (record.count >= maxRequests) {
    return true; // Rate limited
  }
  
  record.count++;
  return false;
};
