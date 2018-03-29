import React, { Component } from 'react'
import Graph from 'react-graph-vis'
import PropTypes from 'prop-types'
import { GraphConverter } from '../Converters/Graph.js'

export class Viewer extends Component
{
    static propTypes = {
        options: PropTypes.object.isRequired,
    }

    state = {
        graph: {
            nodes: [],
            edges: [],
        }
    }
    
    componentDidMount() {
        const converter = new GraphConverter()
        
        fetch('/api/report')
        .then((response) => {
            if(response.status !== 200)
            {
                throw response.statusText
            }
            
            return response.json()
        })
        .then((report) => {
            this.setState({
              graph: converter.convertFromJson(report),
            })
        })
        .catch((error) => {
            console.error(error);
        });
    }
    
    render() {
        const { options } = this.props
        
        return (
            <Graph graph={this.state.graph} options={options} />
        )
    }
}
