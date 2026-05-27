import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CheckCircle, Clock, Users, TrendingDown, AlertTriangle, Target, Globe } from "lucide-react";
import BoardroomAssessment from "@/components/boardroom/BoardroomAssessment";
import ContactSection from "@/components/sections/ContactSection";
import { motion } from "framer-motion";

const BoardroomQuickscan = () => {
  const [language, setLanguage] = useState<'nl' | 'en'>('nl');
  const [timeLeft, setTimeLeft] = useState(17); // Q4 countdown in days
  const [spotsLeft, setSpotsLeft] = useState(3); // Available slots

  useEffect(() => {
    const interval = setInterval(() => {
      setTimeLeft(prev => prev > 0 ? prev - 1 : 0);
    }, 86400000); // Update daily
    return () => clearInterval(interval);
  }, []);

  const content = {
    nl: {
      title: "Je board verwacht controle. Geen nieuwe campagnes.",
      subtitle: "Het UNIFY Framework™ helpt CMO's hun positie te verstevigen met AI en budgetten te verdedigen.",
      hero: "Verlies je vertrouwen omdat je niet kunt aantonen waar marketing écht lekt?",
      pain1: "Waar marketing écht lekt en waar niet dankzij AI",
      pain2: "Hoe elke euro bijdraagt aan omzet (of verdampt)",
      pain3: "Welke AI-systemen je concurrent nu al inzet",
      solution: "Daarom bestaat het UNIFY Framework™: een boardroom-systeem dat CMO's helpt controle te tonen en hun positie te verstevigen. Dankzij AI. ",
      quickscan: {
        title: "Plan Nu De Gratis Boardroom Quickscan",
        benefit1: "In 20 minuten zie je jouw grootste marketinggaten",
        benefit2: "Je ontvangt direct je eigen boardroom-preview (géén pitch)",
        benefit3: "Exclusief voor CMO's"
      },
      cta: "Plan Mijn Boardroom Quickscan",
      urgency: `Q4 budget verdediging sluit in ${timeLeft} dagen`,
      scarcity: `Slechts ${spotsLeft} implementatie slots deze maand`,
      testimonial: "Sara, CMO: 'UNIFY geeft ons een voorsprong op de concurrentie en draagt aanzienlijk bij aan onze omzetgroei.'",
      guarantee: "30-dagen resultaat",
      contact: {
        name: "Ben Verschuur",
        phone: "+316 130 132 66",
        email: "info@boomingventure.com"
      },
      warning: "P.S. Juli zit al bijna vol. Wacht niet tot je volgende boardroom meeting je laatste wordt.",
      alternative: "Of ontvang alleen de gids? Klik hier voor het UNIFY Framework™."
    },
    en: {
      title: "Your Boardroom Expects Control. Not Campaigns.",
      subtitle: "The UNIFY Framework™ helps CMOs strengthen their position and defend budgets",
      hero: "Losing boardroom trust because you can't prove where marketing really leaks?",
      pain1: "Where marketing actually leaks and where it doesn't",
      pain2: "How every euro contributes to revenue (or evaporates)",
      pain3: "Which AI systems your competitor is already deploying",
      solution: "That's why the UNIFY Framework™ exists: a boardroom system that helps CMOs show control and strengthen their position. Thanks to AI.",
      quickscan: {
        title: "Schedule Your Free Boardroom Quickscan Now",
        benefit1: "In 20 minutes you'll see your biggest marketing gaps",
        benefit2: "You'll receive your own boardroom preview immediately (no pitch)",
        benefit3: "Exclusive for CMOs"
      },
      cta: "Schedule My Boardroom Quickscan",
      urgency: `Q4 budget defense closes in ${timeLeft} days`,
      scarcity: `Only ${spotsLeft} implementation slots this month`,
      testimonial: "Sarah, CMO: 'UNIFY gives us a competitive edge and significantly contributes to our revenue growth.'",
      guarantee: "30-day results",
      contact: {
        name: "Ben Verschuur",
        phone: "+316 130 132 66",
        email: "info@boomingventure.com"
      },
      warning: "P.S. July slots are almost full. Don't wait until your next boardroom meeting becomes your last.",
      alternative: "Or just receive the guide? Click here for the UNIFY Framework™."
    }
  };

  const currentContent = content[language];

  const handleQuickscanCTA = () => {
    // Navigate to calendar booking or contact form
    window.open('mailto:info@boomingventure.com?subject=Boardroom Quickscan Request&body=I would like to schedule my free Boardroom Quickscan.', '_blank');
  };

  const handleFrameworkCTA = () => {
    // Navigate to existing unify framework page
    window.location.href = '/unify-framework';
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
      {/* Language Toggle */}
      <div className="fixed top-20 right-4 z-50">
        <div className="flex bg-card rounded-lg border shadow-sm">
          <Button
            variant={language === 'nl' ? 'default' : 'ghost'}
            size="sm"
            onClick={() => setLanguage('nl')}
            className="rounded-r-none"
          >
            NL
          </Button>
          <Button
            variant={language === 'en' ? 'default' : 'ghost'}
            size="sm"
            onClick={() => setLanguage('en')}
            className="rounded-l-none"
          >
            EN
          </Button>
        </div>
      </div>

      {/* Urgency Bar */}
      <motion.div 
        initial={{ y: -50, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        className="bg-destructive text-destructive-foreground py-2 text-center text-sm font-medium"
      >
        <div className="flex items-center justify-center gap-2">
          <Clock className="h-4 w-4" />
          {currentContent.urgency} • {currentContent.scarcity}
        </div>
      </motion.div>

      <div className="container mx-auto px-4 py-8 max-w-4xl">
        {/* Hero Section */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-center mb-12"
        >
          <Badge variant="secondary" className="mb-4">
            <Users className="h-3 w-3 mr-1" />
            {language === 'nl' ? 'Exclusief voor CMO\'s' : 'Exclusive for CMOs'}
          </Badge>
          
          <h1 className="boardroom-title text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-primary via-venture-600 to-booming-600 bg-clip-text text-transparent">
            {currentContent.title}
          </h1>
          
          <p className="text-xl text-muted-foreground mb-6 max-w-3xl mx-auto">
            {currentContent.subtitle}
          </p>
          
          <div className="bg-gradient-to-r from-primary/10 to-venture-500/10 border border-primary/30 rounded-lg p-4 mb-6">
            <p className="text-lg font-medium text-primary">
              {language === 'nl' ? 'MIT & BCG: 92% haalt GEEN voordeel uit AI zonder een framework. UNIFY Framework™ is dat framework.' : 'MIT & BCG: 92% DOES NOT gain advantage from AI without a framework. UNIFY Framework™ is that framework.'}
            </p>
          </div>
          
          <div className="bg-muted/50 border border-destructive/20 rounded-lg p-6 mb-8">
            <h2 className="text-2xl font-semibold mb-4 flex items-center justify-center gap-2">
              <AlertTriangle className="h-6 w-6 text-destructive" />
              {currentContent.hero}
            </h2>
          </div>
        </motion.div>

        {/* Pain Points */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
          className="mb-12"
        >
          <p className="text-lg mb-6 text-center">
            {language === 'nl' ? 'Je board kijkt niet naar likes. Ze willen controle. UNIFY laat dat zien.' : 'Your board doesn\'t look at likes. They want control. UNIFY shows that.'}
          </p>
          
          <p className="text-lg mb-6 text-center font-medium">
            {language === 'nl' ? 'Toch verliezen zelfs top-CMO\'s elk kwartaal impact, budget en vertrouwen. Simpelweg omdat ze niet kunnen laten zien:' : 'Yet even top CMOs lose impact, budget and trust every quarter. Simply because they can\'t show:'}
          </p>
          
          <div className="grid md:grid-cols-3 gap-6 mb-8">
            {[currentContent.pain1, currentContent.pain2, currentContent.pain3].map((pain, index) => (
              <Card key={index} className="border-destructive/20">
                <CardContent className="p-6 text-center">
                  <TrendingDown className="h-8 w-8 text-destructive mx-auto mb-4" />
                  <p className="font-medium">{pain}</p>
                </CardContent>
              </Card>
            ))}
          </div>
          
          <p className="text-lg text-center font-medium">
            {currentContent.solution}
          </p>
        </motion.div>

        {/* Use Cases & Implementation */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.3 }}
          className="mb-12"
        >
          {language === 'nl' && (
            <>
              {/* Use Cases Section */}
              <div className="text-center mb-12">
                <Badge variant="default" className="mb-4 text-lg px-4 py-2">
                  💡 Use Cases & Resultaten
                </Badge>
                
                <h2 className="text-3xl font-bold mb-8">
                  Concrete voorbeelden met bewezen cijfers
                </h2>
                
                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                  <Card className="border-primary/20 bg-primary/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-primary">1. Realtime omzet-attributie</h3>
                      <p className="text-sm mb-4">UNIFY™ laat per campagne exact zien welke marketingeuro echt bijdraagt en welke 'verdampen'.</p>
                      <Badge variant="secondary" className="text-xs">10–20% ROI verbetering</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-venture-500/20 bg-venture-500/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-venture-600">2. Predictive lead scoring</h3>
                      <p className="text-sm mb-4">AI-modellen voorspellen welke leads met hoge waarschijnlijkheid converteren of uitvallen.</p>
                      <Badge variant="secondary" className="text-xs">30 dagen voorspelling</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-booming-500/20 bg-booming-500/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-booming-600">3. Content & creativesnelheid</h3>
                      <p className="text-sm mb-4">Generative AI produceert content, social visuals, blogs, e-mails, video's.</p>
                      <Badge variant="secondary" className="text-xs">300% sneller (Microsoft)</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-primary/20 bg-primary/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-primary">4. Hyper-personalisatie</h3>
                      <p className="text-sm mb-4">AI personaliseert content en timing per bezoeker voor optimale conversie.</p>
                      <Badge variant="secondary" className="text-xs">120% hogere conversie</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-venture-500/20 bg-venture-500/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-venture-600">5. Marketing-operaties</h3>
                      <p className="text-sm mb-4">AI-agents ondersteunen bij rapportages, A/B-testen, workflow automatisering.</p>
                      <Badge variant="secondary" className="text-xs">40% snellere opvolging</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-booming-500/20 bg-booming-500/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-booming-600">6. Budget-optimalisatie</h3>
                      <p className="text-sm mb-4">AI past automatisch budgetverdeling aan op basis van realtime performance.</p>
                      <Badge variant="secondary" className="text-xs">60% hogere omzetgroei</Badge>
                    </CardContent>
                  </Card>
                </div>
              </div>

              {/* Results Summary */}
              <div className="bg-gradient-to-r from-primary/10 via-venture-500/10 to-booming-500/10 border border-primary/20 rounded-2xl p-8 mb-12">
                <h3 className="text-2xl font-bold text-center mb-8">Waarom het UNIFY Framework™</h3>
                
                <div className="grid md:grid-cols-2 gap-8">
                  <div>
                    <h4 className="font-semibold mb-4 text-primary">Voor CMO's</h4>
                    <ul className="space-y-2 text-sm">
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                        Board-ready rapportages
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                        Realtime budgetverdediging
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                        AI-readiness met gap-analyse
                      </li>
                    </ul>
                  </div>
                  
                  <div>
                    <h4 className="font-semibold mb-4 text-venture-600">Voor Marketingteams</h4>
                    <ul className="space-y-2 text-sm">
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-venture-600 mt-0.5 flex-shrink-0" />
                        300% snellere output
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-venture-600 mt-0.5 flex-shrink-0" />
                        25-40% efficiency-opslag
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-venture-600 mt-0.5 flex-shrink-0" />
                        Systeemintegratie in één platform
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              {/* 30-Day Implementation */}
              <div className="text-center mb-12">
                <Badge variant="default" className="mb-4 text-lg px-4 py-2">
                  🔧 UNIFY™ in 30 dagen
                </Badge>
                
                <h3 className="text-2xl font-bold mb-8">Concreet implementatieplan</h3>
                
                <div className="grid md:grid-cols-2 gap-6">
                  <Card className="text-left">
                    <CardContent className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <Badge variant="outline" className="text-xs">Week 1-2</Badge>
                        <h4 className="font-semibold">Kick-off & scan</h4>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        AI-agent analyseert bestaande data, audit omzet, gestandaardiseerd generative content testen.
                      </p>
                    </CardContent>
                  </Card>
                  
                  <Card className="text-left">
                    <CardContent className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <Badge variant="outline" className="text-xs">Week 2-3</Badge>
                        <h4 className="font-semibold">Integratie & automatisering</h4>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        Campagne-AI in Ad/CRM; Copilots in meetings en content; meeting analyses live.
                      </p>
                    </CardContent>
                  </Card>
                  
                  <Card className="text-left">
                    <CardContent className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <Badge variant="outline" className="text-xs">Week 3-4</Badge>
                        <h4 className="font-semibold">Schaling & rapportage</h4>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        Board-ready dashboards, KPI-herdefinitie, eerste ROI-rapport, test slimme AI-agents.
                      </p>
                    </CardContent>
                  </Card>
                  
                  <Card className="text-left border-primary/40 bg-primary/5">
                    <CardContent className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <Badge variant="default" className="text-xs">Dag 30</Badge>
                        <h4 className="font-semibold text-primary">Review & planning</h4>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        Presentatie dashboard + roadmap, AI-agents nemen taken over, team traint vervolgstrategieën.
                      </p>
                    </CardContent>
                  </Card>
                </div>
              </div>
            </>
          )}

          {language === 'en' && (
            <>
              {/* Use Cases Section */}
              <div className="text-center mb-12">
                <Badge variant="default" className="mb-4 text-lg px-4 py-2">
                  💡 Use Cases & Results
                </Badge>
                
                <h2 className="text-3xl font-bold mb-8">
                  Concrete examples with proven numbers
                </h2>
                
                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                  <Card className="border-primary/20 bg-primary/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-primary">1. Realtime revenue attribution</h3>
                      <p className="text-sm mb-4">UNIFY™ shows exactly which marketing euros contribute and which 'evaporate' per campaign.</p>
                      <Badge variant="secondary" className="text-xs">10–20% ROI improvement</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-venture-500/20 bg-venture-500/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-venture-600">2. Predictive lead scoring</h3>
                      <p className="text-sm mb-4">AI models predict which leads are likely to convert or drop out.</p>
                      <Badge variant="secondary" className="text-xs">30-day prediction</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-booming-500/20 bg-booming-500/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-booming-600">3. Content & creative speed</h3>
                      <p className="text-sm mb-4">Generative AI produces content, social visuals, blogs, emails, videos.</p>
                      <Badge variant="secondary" className="text-xs">300% faster (Microsoft)</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-primary/20 bg-primary/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-primary">4. Hyper-personalization</h3>
                      <p className="text-sm mb-4">AI personalizes content and timing per visitor for optimal conversion.</p>
                      <Badge variant="secondary" className="text-xs">120% higher conversion</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-venture-500/20 bg-venture-500/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-venture-600">5. Marketing operations</h3>
                      <p className="text-sm mb-4">AI agents support reporting, A/B testing, workflow automation.</p>
                      <Badge variant="secondary" className="text-xs">40% faster follow-up</Badge>
                    </CardContent>
                  </Card>
                  
                  <Card className="border-booming-500/20 bg-booming-500/5">
                    <CardContent className="p-6">
                      <h3 className="font-semibold mb-3 text-booming-600">6. Budget optimization</h3>
                      <p className="text-sm mb-4">AI automatically adjusts budget allocation based on realtime performance.</p>
                      <Badge variant="secondary" className="text-xs">60% higher revenue growth</Badge>
                    </CardContent>
                  </Card>
                </div>
              </div>

              {/* Results Summary */}
              <div className="bg-gradient-to-r from-primary/10 via-venture-500/10 to-booming-500/10 border border-primary/20 rounded-2xl p-8 mb-12">
                <h3 className="text-2xl font-bold text-center mb-8">Why the UNIFY Framework™</h3>
                
                <div className="grid md:grid-cols-2 gap-8">
                  <div>
                    <h4 className="font-semibold mb-4 text-primary">For CMOs</h4>
                    <ul className="space-y-2 text-sm">
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                        Board-ready reports
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                        Realtime budget defense
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                        AI-readiness with gap analysis
                      </li>
                    </ul>
                  </div>
                  
                  <div>
                    <h4 className="font-semibold mb-4 text-venture-600">For Marketing Teams</h4>
                    <ul className="space-y-2 text-sm">
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-venture-600 mt-0.5 flex-shrink-0" />
                        300% faster output
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-venture-600 mt-0.5 flex-shrink-0" />
                        25-40% efficiency savings
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-4 w-4 text-venture-600 mt-0.5 flex-shrink-0" />
                        System integration in one platform
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              {/* 30-Day Implementation */}
              <div className="text-center mb-12">
                <Badge variant="default" className="mb-4 text-lg px-4 py-2">
                  🔧 UNIFY™ in 30 days
                </Badge>
                
                <h3 className="text-2xl font-bold mb-8">Concrete implementation plan</h3>
                
                <div className="grid md:grid-cols-2 gap-6">
                  <Card className="text-left">
                    <CardContent className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <Badge variant="outline" className="text-xs">Week 1-2</Badge>
                        <h4 className="font-semibold">Kick-off & scan</h4>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        AI agent analyzes existing data, audits revenue, standardized generative content testing.
                      </p>
                    </CardContent>
                  </Card>
                  
                  <Card className="text-left">
                    <CardContent className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <Badge variant="outline" className="text-xs">Week 2-3</Badge>
                        <h4 className="font-semibold">Integration & automation</h4>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        Campaign AI in Ad/CRM; Copilots in meetings and content; live meeting analysis.
                      </p>
                    </CardContent>
                  </Card>
                  
                  <Card className="text-left">
                    <CardContent className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <Badge variant="outline" className="text-xs">Week 3-4</Badge>
                        <h4 className="font-semibold">Scaling & reporting</h4>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        Board-ready dashboards, KPI redefinition, first ROI report, test smart AI agents.
                      </p>
                    </CardContent>
                  </Card>
                  
                  <Card className="text-left border-primary/40 bg-primary/5">
                    <CardContent className="p-6">
                      <div className="flex items-center gap-2 mb-3">
                        <Badge variant="default" className="text-xs">Day 30</Badge>
                        <h4 className="font-semibold text-primary">Review & planning</h4>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        Dashboard presentation + roadmap, AI agents take over tasks, team trains follow-up strategies.
                      </p>
                    </CardContent>
                  </Card>
                </div>
              </div>
            </>
          )}
        </motion.div>

        {/* Interactive Assessment Tool */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.4 }}
          className="mb-12"
        >
          <div className="text-center mb-8">
            <Badge variant="default" className="mb-4 text-lg px-4 py-2">
              <Target className="h-4 w-4 mr-2" />
              {language === 'nl' ? 'INTERACTIEVE SCAN' : 'INTERACTIVE SCAN'}
            </Badge>
            
            <h2 className="text-3xl font-bold mb-4">
              {language === 'nl' ? 'Test Je Boardroom Risico Nu' : 'Test Your Boardroom Risk Now'}
            </h2>
            
            <p className="text-lg text-muted-foreground mb-8">
              {language === 'nl' ? 'Ontdek in 3 minuten waar jouw positie kwetsbaar is' : 'Discover in 3 minutes where your position is vulnerable'}
            </p>
          </div>
          
          <BoardroomAssessment language={language} />
        </motion.div>

        {/* Alternative CTA Section */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.6 }}
          className="bg-gradient-to-r from-primary/5 via-venture-500/5 to-booming-500/5 border border-primary/20 rounded-2xl p-8 mb-12"
        >
          <div className="text-center mb-8">
            <h2 className="text-2xl font-bold mb-6">
              {language === 'nl' ? 'Of Plan Direct Een Persoonlijk Gesprek' : 'Or Schedule a Personal Call Directly'}
            </h2>
            
            <div className="grid md:grid-cols-2 gap-4 mb-6">
              <div className="bg-primary/5 border border-primary/20 rounded-lg p-4">
                <h4 className="font-semibold mb-2 text-primary">
                  {language === 'nl' ? '25-40% Meer Rendement' : '25-40% More ROI'}
                </h4>
                <p className="text-sm text-muted-foreground">
                  {language === 'nl' ? 'Dankzij slimme budgetallocatie en automatische optimalisatie' : 'Through smart budget allocation and automatic optimization'}
                </p>
              </div>
              <div className="bg-venture-500/5 border border-venture-500/20 rounded-lg p-4">
                <h4 className="font-semibold mb-2 text-venture-600">
                  {language === 'nl' ? '300% Snellere Content' : '300% Faster Content'}
                </h4>
                <p className="text-sm text-muted-foreground">
                  {language === 'nl' ? 'Generative AI workflows (bron: Microsoft)' : 'Generative AI workflows (source: Microsoft)'}
                </p>
              </div>
            </div>
            
            <div className="grid md:grid-cols-3 gap-6 mb-8">
              {[
                currentContent.quickscan.benefit1,
                currentContent.quickscan.benefit2,
                currentContent.quickscan.benefit3
              ].map((benefit, index) => (
                <div key={index} className="flex items-start gap-3">
                  <CheckCircle className="h-5 w-5 text-primary mt-1 flex-shrink-0" />
                  <span className="text-sm">{benefit}</span>
                </div>
              ))}
            </div>
            
            <Button 
              size="lg" 
              className="text-xl px-8 py-6 mb-4 w-full md:w-auto bg-gradient-to-r from-primary to-venture-600 hover:from-primary/90 hover:to-venture-600/90"
              onClick={() => document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' })}
            >
              {currentContent.cta}
            </Button>
            
            <p className="text-sm text-muted-foreground">
              {language === 'nl' ? 'Geen verplichtingen • Geen pitches • Direct inzicht' : 'No obligations • No pitches • Immediate insights'}
            </p>
          </div>
        </motion.div>

        {/* Social Proof */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.6 }}
          className="bg-card border rounded-lg p-6 mb-8"
        >
          <blockquote className="text-lg italic text-center mb-4">
            "{currentContent.testimonial}"
          </blockquote>
          <p className="text-sm text-muted-foreground text-center">
            {language === 'nl' ? '2M+ omzet impact' : '2M+ revenue impact'}
          </p>
        </motion.div>

        {/* Guarantee */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.8 }}
          className="text-center mb-8"
        >
          <Card className="border-primary/20 bg-primary/5">
            <CardContent className="p-6">
              <h3 className="font-semibold mb-2">
                {language === 'nl' ? '🛡️ Risico-vrije Garantie' : '🛡️ Risk-Free Guarantee'}
              </h3>
              <p className="text-sm">{currentContent.guarantee}</p>
            </CardContent>
          </Card>
        </motion.div>

        {/* Alternative CTA */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 1.0 }}
          className="text-center mb-8"
        >
          <Button 
            variant="outline" 
            onClick={handleFrameworkCTA}
            className="mb-4"
          >
            {currentContent.alternative}
          </Button>
        </motion.div>

        {/* Warning */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 1.2 }}
          className="bg-muted/50 border border-destructive/20 rounded-lg p-6 text-center mb-8"
        >
          <p className="text-sm font-medium text-destructive mb-4">
            {currentContent.warning}
          </p>
        </motion.div>

        {/* Contact Section */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 1.4 }}
        >
          <ContactSection />
        </motion.div>

        {/* Contact Info */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 1.6 }}
          className="text-center mt-8"
        >
          <p className="mb-2 font-medium">{currentContent.contact.name}</p>
          <p className="text-sm text-muted-foreground mb-1">{currentContent.contact.phone}</p>
          <p className="text-sm text-muted-foreground mb-4">{currentContent.contact.email}</p>
          <p className="text-xs text-muted-foreground">UNIFY Framework™ | Booming Venture</p>
        </motion.div>
      </div>
    </div>
  );
};

export default BoardroomQuickscan;