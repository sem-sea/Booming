import { useRef, useEffect } from 'react';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { ParametricGeometry } from 'three/examples/jsm/geometries/ParametricGeometry.js';

interface AnimatedSceneProps {
  className?: string;
}

const AnimatedScene = ({ className }: AnimatedSceneProps) => {
  const containerRef = useRef<HTMLDivElement>(null);
  
  useEffect(() => {
    if (!containerRef.current) return;
    
    const width = containerRef.current.clientWidth;
    const height = containerRef.current.clientHeight;
    
    // Initialize scene, camera, and renderer
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ 
      alpha: true,
      antialias: true
    });
    
    renderer.setSize(width, height);
    renderer.setClearColor(0x000000, 0);
    containerRef.current.appendChild(renderer.domElement);
    
    // Add ambient and directional lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);
    
    const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
    directionalLight.position.set(10, 10, 10);
    scene.add(directionalLight);
    
    // Add spotlight for dramatic lighting
    const spotlight = new THREE.SpotLight(0x0284c7, 2);
    spotlight.position.set(5, 10, 5);
    spotlight.angle = Math.PI / 4;
    spotlight.penumbra = 0.1;
    spotlight.decay = 2;
    spotlight.distance = 50;
    scene.add(spotlight);
    
    // Create a fog effect for dreamy atmosphere
    scene.fog = new THREE.FogExp2(0x0d9488, 0.005);
    
    // Create surreal melting funnel (Dalí-inspired centerpiece)
    const funnelGeometry = new THREE.TorusGeometry(3, 0.8, 32, 100);
    const funnelMaterial = new THREE.MeshPhongMaterial({ 
      color: 0x0284c7,
      emissive: 0x0284c7,
      emissiveIntensity: 0.3,
      transparent: true,
      opacity: 0.9,
      shininess: 100,
      wireframe: false 
    });
    
    // Create Möbius strip for funnel
    const mobiusGeometry = new ParametricGeometry((u, v, target) => {
      u = u * Math.PI * 2;
      v = v * 2 - 1;
      
      const r = 3 + v * Math.cos(u / 2) * 0.8;
      
      target.set(
        r * Math.cos(u),
        v * Math.sin(u / 2) * 1.5,
        r * Math.sin(u)
      );
    }, 64, 32);
    
    const mobiusMaterial = new THREE.MeshPhongMaterial({ 
      color: 0x0ea5e9,
      emissive: 0x0ea5e9,
      emissiveIntensity: 0.3,
      transparent: true,
      opacity: 0.8,
      shininess: 100,
      side: THREE.DoubleSide
    });
    
    const mobius = new THREE.Mesh(mobiusGeometry, mobiusMaterial);
    scene.add(mobius);
    
    // Create floating islands (surreal terrain)
    const islandCount = 5;
    const islands = [];
    
    for (let i = 0; i < islandCount; i++) {
      const islandGeometry = new THREE.SphereGeometry(
        0.5 + Math.random() * 1.5,
        32, 32,
        0, Math.PI * 2,
        0, Math.PI / 2
      );
      
      // Distort the geometry to make it more surreal
      const vertices = islandGeometry.attributes.position;
      for (let j = 0; j < vertices.count; j++) {
        const x = vertices.getX(j);
        const y = vertices.getY(j);
        const z = vertices.getZ(j);
        
        vertices.setX(j, x + (Math.random() - 0.5) * 0.3);
        vertices.setY(j, y + (Math.random() - 0.5) * 0.3);
        vertices.setZ(j, z + (Math.random() - 0.5) * 0.3);
      }
      
      const islandMaterial = new THREE.MeshPhongMaterial({
        color: new THREE.Color(
          0.1 + Math.random() * 0.1,
          0.5 + Math.random() * 0.3,
          0.7 + Math.random() * 0.3
        ),
        flatShading: true
      });
      
      const island = new THREE.Mesh(islandGeometry, islandMaterial);
      
      // Position the islands in a surreal landscape
      const radius = 8 + Math.random() * 4;
      const theta = Math.random() * Math.PI * 2;
      const phi = Math.acos(2 * Math.random() - 1) / 2;
      
      island.position.set(
        radius * Math.sin(phi) * Math.cos(theta),
        -2 - Math.random() * 3,
        radius * Math.sin(phi) * Math.sin(theta)
      );
      
      island.rotation.set(
        Math.random() * Math.PI,
        Math.random() * Math.PI,
        Math.random() * Math.PI
      );
      
      scene.add(island);
      islands.push({
        mesh: island,
        initialY: island.position.y,
        floatSpeed: 0.001 + Math.random() * 0.002,
        floatHeight: 0.2 + Math.random() * 0.5,
        rotationSpeed: {
          x: (Math.random() - 0.5) * 0.001,
          y: (Math.random() - 0.5) * 0.001,
          z: (Math.random() - 0.5) * 0.001
        }
      });
    }
    
    // Create neural paths (flowing data rivers)
    const pathCount = 15;
    const paths = [];
    
    for (let i = 0; i < pathCount; i++) {
      // Create curved path
      const curve = new THREE.CatmullRomCurve3([
        new THREE.Vector3(
          (Math.random() - 0.5) * 16,
          (Math.random() - 0.5) * 16,
          (Math.random() - 0.5) * 16
        ),
        new THREE.Vector3(
          (Math.random() - 0.5) * 16,
          (Math.random() - 0.5) * 16,
          (Math.random() - 0.5) * 16
        ),
        new THREE.Vector3(
          (Math.random() - 0.5) * 16,
          (Math.random() - 0.5) * 16,
          (Math.random() - 0.5) * 16
        ),
        new THREE.Vector3(
          (Math.random() - 0.5) * 16,
          (Math.random() - 0.5) * 16,
          (Math.random() - 0.5) * 16
        )
      ]);
      
      const pathGeometry = new THREE.TubeGeometry(curve, 64, 0.05, 8, false);
      const pathMaterial = new THREE.MeshPhongMaterial({
        color: new THREE.Color(
          0.5 + Math.random() * 0.5,
          0.5 + Math.random() * 0.5,
          0.5 + Math.random() * 0.5
        ),
        transparent: true,
        opacity: 0.6,
        emissive: 0x0ea5e9,
        emissiveIntensity: 0.3
      });
      
      const path = new THREE.Mesh(pathGeometry, pathMaterial);
      scene.add(path);
      
      paths.push({
        mesh: path,
        pulseSpeed: 0.005 + Math.random() * 0.015
      });
    }
    
    // Create floating symbolic elements (clocks, formulas, etc.)
    const symbolCount = 10;
    const symbols = [];
    
    for (let i = 0; i < symbolCount; i++) {
      let symbolGeometry;
      const symbolType = Math.floor(Math.random() * 5);
      
      switch (symbolType) {
        case 0:
          // Clock (using cylinder)
          symbolGeometry = new THREE.CylinderGeometry(0.5, 0.5, 0.1, 32);
          break;
        case 1:
          // Formula (using flat box)
          symbolGeometry = new THREE.BoxGeometry(0.8, 0.4, 0.05);
          break;
        case 2:
          // Data point (using sphere)
          symbolGeometry = new THREE.SphereGeometry(0.3, 16, 16);
          break;
        case 3:
          // Arrow (using cone)
          symbolGeometry = new THREE.ConeGeometry(0.3, 0.8, 16);
          break;
        default:
          // Node (using octahedron)
          symbolGeometry = new THREE.OctahedronGeometry(0.4);
      }
      
      const symbolMaterial = new THREE.MeshPhongMaterial({
        color: new THREE.Color(
          0.7 + Math.random() * 0.3,
          0.7 + Math.random() * 0.3,
          0.7 + Math.random() * 0.3
        ),
        transparent: true,
        opacity: 0.7,
        emissive: 0x0d9488,
        emissiveIntensity: 0.2
      });
      
      const symbol = new THREE.Mesh(symbolGeometry, symbolMaterial);
      
      // Position symbols in space
      const radius = 5 + Math.random() * 10;
      const theta = Math.random() * Math.PI * 2;
      const phi = Math.acos(2 * Math.random() - 1);
      
      symbol.position.set(
        radius * Math.sin(phi) * Math.cos(theta),
        radius * Math.sin(phi) * Math.sin(theta),
        radius * Math.cos(phi)
      );
      
      scene.add(symbol);
      
      symbols.push({
        mesh: symbol,
        basePosition: symbol.position.clone(),
        floatSpeed: 0.003 + Math.random() * 0.007,
        floatDistance: 0.3 + Math.random() * 0.7,
        rotationSpeed: {
          x: (Math.random() - 0.5) * 0.01,
          y: (Math.random() - 0.5) * 0.01,
          z: (Math.random() - 0.5) * 0.01
        }
      });
    }
    
    // Set camera position
    camera.position.z = 10;
    
    // Add subtle interactivity with orbit controls
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.rotateSpeed = 0.5;
    controls.enableZoom = false;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 0.3;
    
    // Track mouse for interactive elements
    let mouseX = 0;
    let mouseY = 0;
    
    document.addEventListener('mousemove', (event) => {
      // Normalize mouse coordinates (-1 to 1)
      mouseX = (event.clientX / window.innerWidth) * 2 - 1;
      mouseY = -(event.clientY / window.innerHeight) * 2 + 1;
    });
    
    // Animation loop with surreal effects
    let time = 0;
    const animate = () => {
      requestAnimationFrame(animate);
      time += 0.01;
      
      // Animate the Möbius strip (centerpiece)
      mobius.rotation.x = time * 0.1;
      mobius.rotation.y = time * 0.15;
      
      // Apply subtle distortion to the Möbius strip
      const vertices = mobiusGeometry.attributes.position;
      for (let i = 0; i < vertices.count; i++) {
        const idx = i * 3;
        const originalX = vertices.array[idx];
        const originalY = vertices.array[idx + 1];
        const originalZ = vertices.array[idx + 2];
        
        // Apply a sine wave distortion
        const distortion = Math.sin(time + i * 0.1) * 0.05;
        vertices.array[idx] = originalX + distortion;
        vertices.array[idx + 1] = originalY + distortion;
        vertices.array[idx + 2] = originalZ + distortion;
      }
      vertices.needsUpdate = true;
      
      // Animate floating islands
      islands.forEach((island) => {
        // Make islands float up and down
        island.mesh.position.y = island.initialY + Math.sin(time * island.floatSpeed) * island.floatHeight;
        
        // Rotate islands slowly
        island.mesh.rotation.x += island.rotationSpeed.x;
        island.mesh.rotation.y += island.rotationSpeed.y;
        island.mesh.rotation.z += island.rotationSpeed.z;
      });
      
      // Animate data paths
      paths.forEach((path) => {
        // Make paths pulse with varying opacity
        path.mesh.material.opacity = 0.3 + Math.sin(time * path.pulseSpeed) * 0.3;
      });
      
      // Animate symbolic elements
      symbols.forEach((symbol) => {
        // Floating motion
        const floatOffset = Math.sin(time * symbol.floatSpeed) * symbol.floatDistance;
        symbol.mesh.position.set(
          symbol.basePosition.x + floatOffset * 0.2,
          symbol.basePosition.y + floatOffset,
          symbol.basePosition.z + floatOffset * 0.2
        );
        
        // Rotation
        symbol.mesh.rotation.x += symbol.rotationSpeed.x;
        symbol.mesh.rotation.y += symbol.rotationSpeed.y;
        symbol.mesh.rotation.z += symbol.rotationSpeed.z;
        
        // React to mouse position
        const distanceToMouse = Math.sqrt(
          Math.pow((symbol.mesh.position.x / 10) - mouseX, 2) + 
          Math.pow((symbol.mesh.position.y / 10) - mouseY, 2)
        );
        
        if (distanceToMouse < 0.5) {
          // Make symbols glow when mouse is nearby
          symbol.mesh.material.emissiveIntensity = 0.5;
          // Pull slightly toward mouse
          symbol.mesh.position.x += (mouseX - symbol.mesh.position.x / 10) * 0.01;
          symbol.mesh.position.y += (mouseY - symbol.mesh.position.y / 10) * 0.01;
        } else {
          symbol.mesh.material.emissiveIntensity = 0.2;
        }
      });
      
      // Apply influence of mouse movement
      scene.rotation.y += (mouseX * 0.01 - scene.rotation.y) * 0.01;
      scene.rotation.x += (mouseY * 0.01 - scene.rotation.x) * 0.01;
      
      // Apply influence of scroll for ambient color shifts
      const scrollY = window.scrollY;
      const maxScroll = 1000; // Adjust based on page length
      const scrollProgress = Math.min(scrollY / maxScroll, 1);
      
      // Color shift based on scroll position (night > dawn > day)
      const bgColor = new THREE.Color(
        0.05 + scrollProgress * 0.1,
        0.05 + scrollProgress * 0.2,
        0.1 + scrollProgress * 0.3
      );
      scene.fog.color = bgColor;
      
      controls.update();
      renderer.render(scene, camera);
    };
    
    animate();
    
    // Handle window resize
    const handleResize = () => {
      if (!containerRef.current) return;
      
      const width = containerRef.current.clientWidth;
      const height = containerRef.current.clientHeight;
      
      renderer.setSize(width, height);
      camera.aspect = width / height;
      camera.updateProjectionMatrix();
    };
    
    window.addEventListener('resize', handleResize);
    
    // Handle scroll events
    const handleScroll = () => {
      const scrollY = window.scrollY;
      const maxScroll = 500;
      const scrollFactor = Math.min(scrollY / maxScroll, 1);
      
      // Scale and rotate the visualization based on scroll
      scene.rotation.x = scrollFactor * 0.5;
      mobius.rotation.z = scrollFactor * Math.PI;
      camera.position.z = 10 - scrollFactor * 3;
    };
    
    window.addEventListener('scroll', handleScroll);
    
    // Cleanup function
    return () => {
      window.removeEventListener('resize', handleResize);
      window.removeEventListener('scroll', handleScroll);
      
      if (containerRef.current) {
        containerRef.current.removeChild(renderer.domElement);
      }
      
      // Dispose geometries and materials
      mobiusGeometry.dispose();
      mobiusMaterial.dispose();
      
      islands.forEach(island => {
        island.mesh.geometry.dispose();
        island.mesh.material.dispose();
      });
      
      paths.forEach(path => {
        path.mesh.geometry.dispose();
        path.mesh.material.dispose();
      });
      
      symbols.forEach(symbol => {
        symbol.mesh.geometry.dispose();
        symbol.mesh.material.dispose();
      });
      
      renderer.dispose();
    };
  }, []);
  
  return <div ref={containerRef} className={className} />;
};

export default AnimatedScene;
