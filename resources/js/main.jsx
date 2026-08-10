import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.jsx';
import '../css/app.css';

const root = createRoot(document.getElementById('root'));
root.render(<App />);
