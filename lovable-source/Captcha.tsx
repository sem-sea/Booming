
import { useEffect, useRef, useState } from "react";

interface CaptchaProps {
  onVerify: (token: string | null) => void;
  onExpired?: () => void;
  onError?: () => void;
  size?: 'compact' | 'normal';
  theme?: 'light' | 'dark';
}

const Captcha = ({ 
  onVerify, 
  onExpired, 
  onError, 
  size = 'normal', 
  theme = 'light' 
}: CaptchaProps) => {
  const captchaRef = useRef<HTMLDivElement>(null);
  const [widgetId, setWidgetId] = useState<number | null>(null);
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    // Load reCAPTCHA script
    const loadRecaptcha = () => {
      if (window.grecaptcha) {
        setIsLoaded(true);
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoad&render=explicit';
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);

      // Global callback for when reCAPTCHA loads
      (window as any).onRecaptchaLoad = () => {
        setIsLoaded(true);
      };
    };

    loadRecaptcha();

    return () => {
      // Cleanup
      if (widgetId !== null && window.grecaptcha) {
        try {
          window.grecaptcha.reset(widgetId);
        } catch (error) {
          console.warn('Error resetting reCAPTCHA:', error);
        }
      }
    };
  }, []);

  useEffect(() => {
    if (isLoaded && captchaRef.current && !widgetId) {
      try {
        const id = window.grecaptcha.render(captchaRef.current, {
          sitekey: '6Ldtk2MrAAAAAClQvb9OLQ8PYeX4--OS_ss8dfcr',
          callback: onVerify,
          'expired-callback': onExpired,
          'error-callback': onError,
          size,
          theme
        });
        setWidgetId(id);
      } catch (error) {
        console.error('Error rendering reCAPTCHA:', error);
        onError?.();
      }
    }
  }, [isLoaded, onVerify, onExpired, onError, size, theme, widgetId]);

  const reset = () => {
    if (widgetId !== null && window.grecaptcha) {
      try {
        window.grecaptcha.reset(widgetId);
      } catch (error) {
        console.warn('Error resetting reCAPTCHA:', error);
      }
    }
  };

  // Expose reset method
  useEffect(() => {
    (captchaRef.current as any)?.setAttribute('data-reset', reset);
  }, [reset]);

  return (
    <div className="captcha-container">
      <div ref={captchaRef}></div>
      {!isLoaded && (
        <div className="flex items-center justify-center p-4 bg-gray-100 rounded">
          <div className="text-sm text-gray-600">Loading security verification...</div>
        </div>
      )}
    </div>
  );
};

// Type declaration for reCAPTCHA
declare global {
  interface Window {
    grecaptcha: {
      render: (container: HTMLElement, options: any) => number;
      reset: (widgetId?: number) => void;
      getResponse: (widgetId?: number) => string;
    };
  }
}

export default Captcha;
