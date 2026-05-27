
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Menu, X, Calculator, TrendingUp, BookOpen, ChevronDown } from "lucide-react";
import { cn } from "@/lib/utils";
import { Link, useNavigate } from "react-router-dom";
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuList,
  NavigationMenuTrigger,
} from "@/components/ui/navigation-menu";

const Navbar = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const navigate = useNavigate();

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen);
  };

  const scrollToContact = () => {
    if (window.location.pathname !== '/') {
      navigate('/#contact');
      setTimeout(() => {
        const contactSection = document.querySelector('#contact');
        if (contactSection) {
          contactSection.scrollIntoView({ behavior: 'smooth' });
        }
      }, 100);
    } else {
      const contactSection = document.querySelector('#contact');
      if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth' });
      }
    }
  };

  const navItems = [
    {
      label: "About",
      href: "/about",
      submenu: [
        { label: "About Us", href: "/about" },
        { label: "UNIFY Framework™", href: "/unify-framework" }
      ]
    },
    { label: "Services", href: "/services" },
    { label: "Funnel Leak Calculator", href: "/funnel-calculator", icon: Calculator },
    { label: "ROI Forecaster", href: "/roi-forecaster", icon: TrendingUp },
    { label: "Contact", href: "#contact", action: scrollToContact },
  ];

  return (
    <nav className="fixed top-0 left-0 right-0 bg-white/90 backdrop-blur-md z-50 border-b">
      <div className="container mx-auto px-4 md:px-6 py-4">
        <div className="flex items-center justify-between">
          <Link to="/" className="flex items-center">
            <span className="text-2xl font-bold gradient-text">
              Booming<span className="text-venture-600">Venture</span>
            </span>
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden md:flex md:items-center md:space-x-16">
            <NavigationMenu>
              <NavigationMenuList className="space-x-4">
                {navItems.map((item) => (
                  <NavigationMenuItem key={item.label}>
                    {item.submenu ? (
                      <>
                        <NavigationMenuTrigger className="text-foreground hover:text-booming-600 transition-colors font-medium flex items-center gap-2">
                          {item.icon && <item.icon className="h-4 w-4" />}
                          {item.label}
                        </NavigationMenuTrigger>
                        <NavigationMenuContent>
                          <div className="grid w-48 gap-1 p-2">
                            {item.submenu.map((subItem) => (
                              <Link
                                key={subItem.label}
                                to={subItem.href}
                                className="block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground"
                              >
                                <div className="text-sm font-medium leading-none">{subItem.label}</div>
                              </Link>
                            ))}
                          </div>
                        </NavigationMenuContent>
                      </>
                    ) : item.action ? (
                      <button
                        onClick={item.action}
                        className="text-foreground hover:text-booming-600 transition-colors font-medium flex items-center gap-2"
                      >
                        {item.icon && <item.icon className="h-4 w-4" />}
                        {item.label}
                      </button>
                    ) : (
                      <Link
                        to={item.href}
                        className="text-foreground hover:text-booming-600 transition-colors font-medium flex items-center gap-2"
                      >
                        {item.icon && <item.icon className="h-4 w-4" />}
                        {item.label}
                      </Link>
                    )}
                  </NavigationMenuItem>
                ))}
              </NavigationMenuList>
            </NavigationMenu>

            <Button
              className="bg-booming-600 hover:bg-booming-700"
              onClick={scrollToContact}
            >
              Get Started
            </Button>
          </div>

          {/* Mobile Navigation Toggle */}
          <button
            className="md:hidden text-foreground focus:outline-none"
            onClick={toggleMenu}
            aria-label="Toggle menu"
          >
            {isMenuOpen ? (
              <X className="h-6 w-6" />
            ) : (
              <Menu className="h-6 w-6" />
            )}
          </button>
        </div>
      </div>

      {/* Mobile Navigation Menu */}
      <div
        className={cn(
          "md:hidden transition-all duration-300 ease-in-out overflow-hidden",
          isMenuOpen ? "max-h-96" : "max-h-0"
        )}
      >
        <div className="container mx-auto px-4 pb-4 flex flex-col space-y-3">
          {navItems.map((item) => (
            <div key={item.label}>
              {item.submenu ? (
                <div className="space-y-2">
                  <div className="text-foreground font-medium py-2 flex items-center gap-2">
                    {item.icon && <item.icon className="h-4 w-4" />}
                    {item.label}
                  </div>
                  <div className="pl-4 space-y-2">
                    {item.submenu.map((subItem) => (
                      <Link
                        key={subItem.label}
                        to={subItem.href}
                        className="block text-foreground/80 hover:text-booming-600 py-1 transition-colors"
                        onClick={() => setIsMenuOpen(false)}
                      >
                        {subItem.label}
                      </Link>
                    ))}
                  </div>
                </div>
              ) : item.action ? (
                <button
                  onClick={() => {
                    item.action();
                    setIsMenuOpen(false);
                  }}
                  className="text-foreground hover:text-booming-600 py-2 transition-colors font-medium flex items-center gap-2"
                >
                  {item.icon && <item.icon className="h-4 w-4" />}
                  {item.label}
                </button>
              ) : (
                <Link
                  to={item.href}
                  className="text-foreground hover:text-booming-600 py-2 transition-colors font-medium flex items-center gap-2"
                  onClick={() => setIsMenuOpen(false)}
                >
                  {item.icon && <item.icon className="h-4 w-4" />}
                  {item.label}
                </Link>
              )}
            </div>
          ))}
          <Button
            className="bg-booming-600 hover:bg-booming-700 w-full"
            onClick={() => {
              scrollToContact();
              setIsMenuOpen(false);
            }}
          >
            Get Started
          </Button>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
