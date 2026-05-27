
// SECURITY: original Lovable export hard-coded a Brevo API key here.
// Redacted before commit. Move to an environment variable (Vite:
// `import.meta.env.VITE_BREVO_API_KEY`) and load from .env (gitignored).
// The original key has been REVOKED in the Brevo dashboard.
const BREVO_API_KEY = (import.meta as any).env?.VITE_BREVO_API_KEY ?? '';
const BREVO_API_URL = 'https://api.brevo.com/v3';

export interface BrevoContact {
  email: string;
  attributes?: {
    FIRSTNAME?: string;
    LASTNAME?: string;
    COMPANY?: string;
    PHONE?: string;
    WEBSITE_VISITORS?: number;
    OPT_IN_RATE?: number;
    CONVERSION_RATE?: number;
    AOV?: number;
    MARKETING_BUDGET?: number;
    LEAD_SOURCE?: string;
    MESSAGE?: string;
    SPAM_SCORE?: string;
    CAPTCHA_VERIFIED?: string;
  };
  listIds?: number[];
}

export const addContactToBrevo = async (contact: BrevoContact): Promise<boolean> => {
  try {
    const response = await fetch(`${BREVO_API_URL}/contacts`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'api-key': BREVO_API_KEY
      },
      body: JSON.stringify({
        email: contact.email,
        attributes: contact.attributes,
        listIds: contact.listIds || [],
        updateEnabled: true
      })
    });

    if (response.ok || response.status === 201) {
      console.log('Contact added to Brevo successfully');
      return true;
    } else {
      const errorData = await response.json();
      console.error('Brevo API error:', errorData);
      return false;
    }
  } catch (error) {
    console.error('Error adding contact to Brevo:', error);
    return false;
  }
};

export const sendTransactionalEmail = async (
  to: string,
  templateId: number,
  params?: Record<string, any>
): Promise<boolean> => {
  try {
    const response = await fetch(`${BREVO_API_URL}/smtp/email`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'api-key': BREVO_API_KEY
      },
      body: JSON.stringify({
        to: [{ email: to }],
        templateId,
        params
      })
    });

    if (response.ok) {
      console.log('Email sent successfully via Brevo');
      return true;
    } else {
      const errorData = await response.json();
      console.error('Brevo email API error:', errorData);
      return false;
    }
  } catch (error) {
    console.error('Error sending email via Brevo:', error);
    return false;
  }
};
