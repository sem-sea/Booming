
import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { X, Cookie, Settings } from "lucide-react";
import { Link } from "react-router-dom";

const CookieConsent = () => {
  const [showBanner, setShowBanner] = useState(false);
  const [showSettings, setShowSettings] = useState(false);
  const [preferences, setPreferences] = useState({
    necessary: true, // Always true, non-toggleable
    analytics: false,
    marketing: false,
    functional: false
  });

  useEffect(() => {
    const consent = localStorage.getItem('cookie-consent');
    if (!consent) {
      setShowBanner(true);
    } else {
      const savedPreferences = JSON.parse(consent);
      setPreferences(savedPreferences);
    }
  }, []);

  const acceptAll = () => {
    const allAccepted = {
      necessary: true,
      analytics: true,
      marketing: true,
      functional: true
    };
    setPreferences(allAccepted);
    localStorage.setItem('cookie-consent', JSON.stringify(allAccepted));
    localStorage.setItem('cookie-consent-date', new Date().toISOString());
    setShowBanner(false);
    setShowSettings(false);
  };

  const acceptNecessary = () => {
    const necessaryOnly = {
      necessary: true,
      analytics: false,
      marketing: false,
      functional: false
    };
    setPreferences(necessaryOnly);
    localStorage.setItem('cookie-consent', JSON.stringify(necessaryOnly));
    localStorage.setItem('cookie-consent-date', new Date().toISOString());
    setShowBanner(false);
    setShowSettings(false);
  };

  const savePreferences = () => {
    localStorage.setItem('cookie-consent', JSON.stringify(preferences));
    localStorage.setItem('cookie-consent-date', new Date().toISOString());
    setShowBanner(false);
    setShowSettings(false);
  };

  const handlePreferenceChange = (key: keyof typeof preferences) => {
    if (key === 'necessary') return; // Can't disable necessary cookies
    setPreferences(prev => ({
      ...prev,
      [key]: !prev[key]
    }));
  };

  if (!showBanner) return null;

  return (
    <>
      {/* Cookie Banner */}
      <div className="fixed bottom-0 left-0 right-0 z-50 p-4 bg-white border-t shadow-lg">
        <div className="container mx-auto max-w-6xl">
          <Card className="border-0 shadow-none">
            <CardContent className="p-6">
              <div className="flex items-start gap-4">
                <Cookie className="h-6 w-6 text-booming-600 mt-1 shrink-0" />
                <div className="flex-1">
                  <h3 className="font-semibold text-lg mb-2">We use cookies</h3>
                  <p className="text-sm text-muted-foreground mb-4">
                    We use cookies to enhance your browsing experience, serve personalized content, 
                    and analyze our traffic. By clicking "Accept All", you consent to our use of cookies. 
                    You can manage your preferences or learn more in our{" "}
                    <Link to="/privacy-policy" className="text-booming-600 hover:underline">
                      Privacy Policy
                    </Link>.
                  </p>
                  <div className="flex flex-wrap gap-3">
                    <Button onClick={acceptAll} className="bg-booming-600 hover:bg-booming-700">
                      Accept All
                    </Button>
                    <Button onClick={acceptNecessary} variant="outline">
                      Necessary Only
                    </Button>
                    <Button 
                      onClick={() => setShowSettings(true)} 
                      variant="ghost"
                      className="gap-2"
                    >
                      <Settings className="h-4 w-4" />
                      Cookie Settings
                    </Button>
                  </div>
                </div>
                <Button
                  onClick={acceptNecessary}
                  variant="ghost"
                  size="sm"
                  className="shrink-0"
                >
                  <X className="h-4 w-4" />
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      {/* Cookie Settings Modal */}
      {showSettings && (
        <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
          <Card className="w-full max-w-2xl max-h-[80vh] overflow-y-auto">
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold">Cookie Preferences</h2>
                <Button onClick={() => setShowSettings(false)} variant="ghost" size="sm">
                  <X className="h-4 w-4" />
                </Button>
              </div>

              <div className="space-y-6">
                <div className="border-b pb-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h3 className="font-semibold">Necessary Cookies</h3>
                      <p className="text-sm text-muted-foreground">
                        Required for basic website functionality. Cannot be disabled.
                      </p>
                    </div>
                    <div className="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                      Always On
                    </div>
                  </div>
                </div>

                <div className="border-b pb-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h3 className="font-semibold">Analytics Cookies</h3>
                      <p className="text-sm text-muted-foreground">
                        Help us understand how visitors interact with our website.
                      </p>
                    </div>
                    <Button
                      onClick={() => handlePreferenceChange('analytics')}
                      variant={preferences.analytics ? "default" : "outline"}
                      size="sm"
                    >
                      {preferences.analytics ? "Enabled" : "Disabled"}
                    </Button>
                  </div>
                </div>

                <div className="border-b pb-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h3 className="font-semibold">Marketing Cookies</h3>
                      <p className="text-sm text-muted-foreground">
                        Used to track visitors for personalized advertising.
                      </p>
                    </div>
                    <Button
                      onClick={() => handlePreferenceChange('marketing')}
                      variant={preferences.marketing ? "default" : "outline"}
                      size="sm"
                    >
                      {preferences.marketing ? "Enabled" : "Disabled"}
                    </Button>
                  </div>
                </div>

                <div className="border-b pb-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h3 className="font-semibold">Functional Cookies</h3>
                      <p className="text-sm text-muted-foreground">
                        Enable enhanced functionality and personalization.
                      </p>
                    </div>
                    <Button
                      onClick={() => handlePreferenceChange('functional')}
                      variant={preferences.functional ? "default" : "outline"}
                      size="sm"
                    >
                      {preferences.functional ? "Enabled" : "Disabled"}
                    </Button>
                  </div>
                </div>
              </div>

              <div className="flex gap-3 mt-6">
                <Button onClick={savePreferences} className="bg-booming-600 hover:bg-booming-700">
                  Save Preferences
                </Button>
                <Button onClick={acceptAll} variant="outline">
                  Accept All
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      )}
    </>
  );
};

export default CookieConsent;
